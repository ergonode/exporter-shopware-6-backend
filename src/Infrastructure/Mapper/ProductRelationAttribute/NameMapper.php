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
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttributeMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;

class NameMapper implements ProductRelationAttributeMapperInterface
{
    public function map(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $shopware6ProductCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute,
        ?Language $language = null
    ): AbstractProductCrossSelling {
        $name = $relationAttribute->getLabel()->get($language ?: $channel->getDefaultLanguage());

        if ($name) {
            $shopware6ProductCrossSelling->setName($name);
        }

        if (null === $language && null === $name) {
            $shopware6ProductCrossSelling->setName($relationAttribute->getCode()->getValue());
        }

        return $shopware6ProductCrossSelling;
    }
}
