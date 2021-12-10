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
use Ergonode\ExporterShopware6\Infrastructure\Exception\Mapper\Shopware6ExporterProductNoFoundException;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttributeMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Domain\Query\ProductQueryInterface;

class RootProductIdMapper implements ProductRelationAttributeMapperInterface
{
    private ProductRepositoryInterface $shopware6ProductRepository;

    private ProductQueryInterface $productQuery;

    public function __construct(
        ProductRepositoryInterface $shopware6ProductRepository,
        ProductQueryInterface $productQuery
    ) {
        $this->shopware6ProductRepository = $shopware6ProductRepository;
        $this->productQuery = $productQuery;
    }

    /**
     * @throws Shopware6ExporterProductNoFoundException
     */
    public function map(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $shopware6ProductCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute,
        ?Language $language = null
    ): AbstractProductCrossSelling {
        $shopwareId = $this->shopware6ProductRepository->load($channel->getId(), $product->getId());
        if (null === $shopwareId) {
            throw new Shopware6ExporterProductNoFoundException(
                $product->getId(),
                $this->productQuery->findSkuByProductId($product->getId()),
            );
        }

        $shopware6ProductCrossSelling->setProductId($shopwareId);

        return $shopware6ProductCrossSelling;
    }
}
