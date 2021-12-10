<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Builder;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttributeMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;

class ProductRelationAttributeBuilder
{
    /**
     * @var ProductRelationAttributeMapperInterface[]
     */
    private array $collection;

    public function __construct(ProductRelationAttributeMapperInterface ...$collection)
    {
        $this->collection = $collection;
    }

    public function build(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $shopware6ProductCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute,
        ?Language $language = null
    ): AbstractProductCrossSelling {
        foreach ($this->collection as $mapper) {
            $shopware6ProductCrossSelling = $mapper->map(
                $channel,
                $export,
                $shopware6ProductCrossSelling,
                $product,
                $relationAttribute,
                $language,
            );
        }

        return $shopware6ProductCrossSelling;
    }
}
