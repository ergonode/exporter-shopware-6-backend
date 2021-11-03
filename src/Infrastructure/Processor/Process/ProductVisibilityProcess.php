<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Attribute\Domain\Repository\OptionRepositoryInterface;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Attribute\Domain\ValueObject\OptionKey;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\ProductRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Client\ProductVisibilityClient;
use Ergonode\ExporterShopware6\Infrastructure\Exception\ProductAttributeNoFoundException;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6ExporterException;
use Ergonode\ExporterShopware6\Infrastructure\Model\BatchVisibilitiesProduct;
use Ergonode\ExporterShopware6\Infrastructure\Model\VisibilitiesProduct;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\SharedKernel\Domain\AggregateId;

class ProductVisibilityProcess
{
    private const DEFAULT_VISIBILITY = 30;
    private const DEFAULT_LOCALE     = 'en_GB';

    private ProductRepositoryInterface $shopwareProductRepository;

    private OptionRepositoryInterface $optionRepository;

    private ProductVisibilityClient $productVisibilityClient;

    private ExportRepositoryInterface $exportRepository;

    public function __construct(
        ProductRepositoryInterface $shopwareProductRepository,
        OptionRepositoryInterface $optionRepository,
        ProductVisibilityClient $productVisibilityClient,
        ExportRepositoryInterface $exportRepository
    ) {
        $this->shopwareProductRepository = $shopwareProductRepository;
        $this->optionRepository = $optionRepository;
        $this->productVisibilityClient = $productVisibilityClient;
        $this->exportRepository = $exportRepository;
    }

    public function process(
        ExportLineId $lineId,
        Export $export,
        Shopware6Channel $channel,
        AbstractProduct $product,
        AttributeCode $attributeCode
    ): void {

        try {
            $shopwareProductId = $this->shopwareProductRepository->load($channel->getId(), $product->getId());

            $value = $this->getValue($product, $attributeCode);
            if ($shopwareProductId && !empty($value)) {
                $visibility = $this->productVisibilityClient->get($channel, $shopwareProductId);
                $this->updateVisibility($channel, $shopwareProductId, $visibility ?? [], $value);
            }
        } catch (Shopware6ExporterException $exception) {
            $this->exportRepository->addError($export->getId(), $exception->getMessage(), $exception->getParameters());
        }

        $this->exportRepository->processLine($lineId);
    }

    /**
     * @param Shopware6Channel $channel
     * @param string $shopwareProductId
     * @param VisibilitiesProduct[] $visibility
     * @param OptionKey[] $value
     */
    private function updateVisibility(
        Shopware6Channel $channel,
        string $shopwareProductId,
        array $visibility,
        array $value
    ): void {
        $this->addVisibilities($channel, $shopwareProductId, $visibility, $value);
        $this->deleteVisibilities($channel, $visibility, $value);
    }

    /**
     * @param AbstractProduct $product
     * @param AttributeCode $attributeCode
     * @return OptionKey[]
     * @throws ProductAttributeNoFoundException
     */
    private function getValue(
        AbstractProduct $product,
        AttributeCode $attributeCode
    ): array {
        if (!$product->hasAttribute($attributeCode)) {
            throw new ProductAttributeNoFoundException($product->getSku(), $attributeCode);
        }
        $value = $product->getAttribute($attributeCode);

        $defaultValue = $value->getValue()[self::DEFAULT_LOCALE] ?? null;
        if (!$defaultValue) {
            return [];
        }
        $options = array_unique(explode(',', $defaultValue));

        $code = [];
        foreach ($options as $optionValue) {
            if (!AggregateId::isValid($optionValue)) {
                continue;
            }
            $optionId = new AggregateId($optionValue);
            $option = $this->optionRepository->load($optionId);
            if ($option) {
                $code[] = $option->getCode();
            }
        }

        return array_unique($code);
    }

    /**
     * @param Shopware6Channel $channel
     * @param string $shopwareProductId
     * @param VisibilitiesProduct[] $visibility
     * @param OptionKey[] $value
     */
    private function addVisibilities(
        Shopware6Channel $channel,
        string $shopwareProductId,
        array $visibility,
        array $value
    ): void {
        $visibilitiesToUpdate = [];
        foreach ($value as $option) {
            $exist = false;
            foreach ($visibility as $visibilitiesProduct) {
                if ($visibilitiesProduct->getSalesChannelId() === $option->getValue()) {
                    $exist = true;
                    break;
                }
            }
            if (!$exist) {
                $visibilitiesToUpdate[] = new VisibilitiesProduct(
                    null,
                    $shopwareProductId,
                    $option->getValue(),
                    self::DEFAULT_VISIBILITY,
                );
            }
        }

        if (!empty($visibilitiesToUpdate)) {
            $batchVisibilities = new BatchVisibilitiesProduct(
                $visibilitiesToUpdate,
                BatchVisibilitiesProduct::ACTION_UPSERT,
            );
            $this->productVisibilityClient->delete($channel, $batchVisibilities);
        }
    }

    /**
     * @param Shopware6Channel $channel
     * @param VisibilitiesProduct[] $visibility
     * @param OptionKey[] $value
     */
    private function deleteVisibilities(
        Shopware6Channel $channel,
        array $visibility,
        array $value
    ): void {
        $visibilitiesToDelete = [];
        foreach ($visibility as $visibilitiesProduct) {
            $exist = false;
            foreach ($value as $option) {
                if ($visibilitiesProduct->getSalesChannelId() === $option->getValue()) {
                    $exist = true;
                    break;
                }
            }
            if (!$exist) {
                $visibilitiesToDelete[] = new VisibilitiesProduct($visibilitiesProduct->getId());
            }
        }

        if (!empty($visibilitiesToDelete)) {
            $batchVisibilities = new BatchVisibilitiesProduct(
                $visibilitiesToDelete,
                BatchVisibilitiesProduct::ACTION_DELETE,
            );
            $this->productVisibilityClient->delete($channel, $batchVisibilities);
        }
    }
}
