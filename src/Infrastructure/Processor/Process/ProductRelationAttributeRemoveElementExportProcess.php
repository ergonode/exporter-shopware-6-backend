<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Query\ProductQueryInterface;
use Ergonode\ExporterShopware6\Domain\Repository\ProductRelationAttributeRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Client\ProductRelationAttributeClient;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use GuzzleHttp\Exception\ClientException;

class ProductRelationAttributeRemoveElementExportProcess
{
    private ProductRelationAttributeRepositoryInterface $productRelationAttributeRepository;

    private ProductRelationAttributeClient $relationAttributeClient;

    private TranslationInheritanceCalculator $calculator;

    private ProductQueryInterface $productQuery;

    public function __construct(
        ProductRelationAttributeRepositoryInterface $productRelationAttributeRepository,
        ProductRelationAttributeClient $relationAttributeClient,
        TranslationInheritanceCalculator $calculator,
        ProductQueryInterface $productQuery
    ) {
        $this->productRelationAttributeRepository = $productRelationAttributeRepository;
        $this->relationAttributeClient = $relationAttributeClient;
        $this->calculator = $calculator;
        $this->productQuery = $productQuery;
    }

    public function process(
        ExportLineId $lineId,
        Export $export,
        Shopware6Channel $channel,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute
    ): void {

        $productCrossSelling = $this->loadProductCrossSelling($channel, $product->getId(), $relationAttribute->getId());
        if (null === $productCrossSelling) {
            return;
        }

        $shopwareProductIds = $this->loadShopwareProductIds($channel, $product, $relationAttribute);

        $deleteShopwareIds = $this->differenceMatcher($productCrossSelling, $shopwareProductIds);


        foreach ($deleteShopwareIds as $shopwareId) {
            $this->relationAttributeClient->deleteAssignedProduct(
                $channel,
                $productCrossSelling->getId(),
                $shopwareId,
            );
        }
    }

    /**
     * @param string[] $shopwareIds
     *
     * @return string[]
     */
    private function differenceMatcher(AbstractProductCrossSelling $productCrossSelling, array $shopwareIds): array
    {
        $differenceIds = [];
        foreach ($productCrossSelling->getAssignedProducts() as $assignedProduct) {
            if (in_array($assignedProduct->getProductId(), $shopwareIds, true)) {
                continue;
            }
            $differenceIds[] = $assignedProduct->getId();
        }

        return $differenceIds;
    }

    /**
     * @return string[]
     */
    private function loadShopwareProductIds(
        Shopware6Channel $channel,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute
    ): array {
        if (false === $product->hasAttribute($relationAttribute->getCode())) {
            return [];
        }

        $value = $product->getAttribute($relationAttribute->getCode());
        $calculateValue = $this->calculator->calculate(
            $relationAttribute->getScope(),
            $value,
            $channel->getDefaultLanguage(),
        );
        if (empty($calculateValue)) {
            return [];
        }

        $productIds = array_map(
            static fn(string $item) => new ProductId($item),
            $calculateValue,
        );

        return $this->productQuery->findShopwareIdByProductIds($channel->getId(), $productIds);
    }

    private function loadProductCrossSelling(
        Shopware6Channel $channel,
        ProductId $productId,
        AttributeId $attributeId,
        ?Shopware6Language $shopware6Language = null
    ): ?AbstractProductCrossSelling {
        $shopwareId = $this->productRelationAttributeRepository->load($channel->getId(), $productId, $attributeId);
        if ($shopwareId) {
            try {
                return $this->relationAttributeClient->get($channel, $shopwareId, $shopware6Language);
            } catch (ClientException $exception) {
            }
        }

        return null;
    }
}
