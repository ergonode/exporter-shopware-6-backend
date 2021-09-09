<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;

interface ProductRelationAttributeMapperInterface
{
    public function map(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $shopware6ProductCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $relationAttribute,
        ?Language $language = null
    ): AbstractProductCrossSelling;
}
