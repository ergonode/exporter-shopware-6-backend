<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\LanguageRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Builder\ProductBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6ProductClient;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6ExporterException;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Webmozart\Assert\Assert;

class ProductShopware6ExportProcess
{
    private ProductBuilder $builder;

    private Shopware6ProductClient $productClient;

    private LanguageRepositoryInterface $languageRepository;

    private ExportRepositoryInterface $exportRepository;

    public function __construct(
        ProductBuilder $builder,
        Shopware6ProductClient $productClient,
        LanguageRepositoryInterface $languageRepository,
        ExportRepositoryInterface $exportRepository
    ) {
        $this->builder = $builder;
        $this->productClient = $productClient;
        $this->languageRepository = $languageRepository;
        $this->exportRepository = $exportRepository;
    }

    /**
     * @throws \Exception
     */
    public function process(
        ExportLineId $lineId,
        Export $export,
        Shopware6Channel $channel,
        AbstractProduct $product
    ): void {
        $shopwareProduct = $this->productClient->find($channel, $product);

        try {
            if ($shopwareProduct) {
                $this->updateProduct($channel, $export, $shopwareProduct, $product);
            } else {
                $shopwareProduct = new Shopware6Product();
                $this->builder->build($channel, $export, $shopwareProduct, $product);
                $this->productClient->insert($channel, $shopwareProduct, $product->getId());
            }

            foreach ($channel->getLanguages() as $language) {
                if ($this->languageRepository->exists($channel->getId(), $language->getCode())) {
                    $this->updateProductWithLanguage($channel, $export, $language, $product, $shopwareProduct);
                }
            }
        } catch (Shopware6ExporterException $exception) {
            $this->exportRepository->addError($export->getId(), $exception->getMessage(), $exception->getParameters());
        }
        $this->exportRepository->processLine($lineId);
    }

    private function updateProduct(
        Shopware6Channel $channel,
        Export $export,
        Shopware6Product $shopwareProduct,
        AbstractProduct $product,
        ?Language $language = null,
        ?Shopware6Language $shopwareLanguage = null
    ): void {
        $this->builder->build($channel, $export, $shopwareProduct, $product, $language);

        if ($shopwareProduct->isModified() || $shopwareProduct->hasItemToRemoved()) {
            $this->productClient->update($channel, $shopwareProduct, $shopwareLanguage);
        }
    }

    /**
     * @throws \Exception
     */
    private function updateProductWithLanguage(
        Shopware6Channel $channel,
        Export $export,
        Language $language,
        AbstractProduct $product,
        Shopware6Product $shopware6Product
    ): void {
        $shopwareLanguage = $this->languageRepository->load($channel->getId(), $language->getCode());
        Assert::notNull($shopwareLanguage);

        $translatedShopwareProduct = $shopware6Product->getTranslated($shopwareLanguage);
        $this->updateProduct($channel, $export, $translatedShopwareProduct, $product, $language, $shopwareLanguage);
    }
}
