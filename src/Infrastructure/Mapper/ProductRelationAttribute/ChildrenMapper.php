<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttribute;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\ProductRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttributeMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\ExporterShopware6\Infrastructure\Model\Basic\AssignedProduct;
use Ergonode\ExporterShopware6\Infrastructure\Model\ProductCrossSelling\AbstractAssignedProduct;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;

class ChildrenMapper implements ProductRelationAttributeMapperInterface
{
    private TranslationInheritanceCalculator $calculator;

    private ProductRepositoryInterface $shopware6ProductRepository;

    public function __construct(
        TranslationInheritanceCalculator $calculator,
        ProductRepositoryInterface $shopware6ProductRepository
    ) {
        $this->calculator = $calculator;
        $this->shopware6ProductRepository = $shopware6ProductRepository;
    }

    public function map(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $shopware6ProductCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute,
        ?Language $language = null
    ): AbstractProductCrossSelling {

        if (false === $product->hasAttribute($relationAttribute->getCode())) {
            return $shopware6ProductCrossSelling;
        }

        $value = $product->getAttribute($relationAttribute->getCode());
        $calculateValue = $this->calculator->calculate(
            $relationAttribute->getScope(),
            $value,
            $channel->getDefaultLanguage(),
        );
        if ($calculateValue) {
            $position = $this->getCurrentPosition($shopware6ProductCrossSelling);
            foreach ($calculateValue as $relationValue) {
                $relationProductId = new ProductId($relationValue);
                $assignedProduct = $this->mapElement(
                    $channel,
                    $relationProductId,
                    ++$position,
                );

                if ($assignedProduct) {
                    $shopware6ProductCrossSelling->addAssignedProduct($assignedProduct);
                }
            }
        }

        return $shopware6ProductCrossSelling;
    }

    private function mapElement(
        Shopware6Channel $channel,
        ProductId $productId,
        int $position = 1
    ): ?AbstractAssignedProduct {
        $shopwareId = $this->shopware6ProductRepository->load($channel->getId(), $productId);
        if ($shopwareId) {
            return new AssignedProduct(
                null,
                $shopwareId,
                $position,
            );
        }

        return null;
    }

    private function getCurrentPosition(AbstractProductCrossSelling $shopware6ProductCrossSelling): int
    {
        $position = 0;
        foreach ($shopware6ProductCrossSelling->getAssignedProducts() as $assigned) {
            if ($assigned->getPosition() > $position) {
                $position = $assigned->getPosition();
            }
        }

        return $position;
    }
}
