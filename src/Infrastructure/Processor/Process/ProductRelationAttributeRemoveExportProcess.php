<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Query\ProductRelationAttributeQueryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Client\ProductRelationAttributeClient;

class ProductRelationAttributeRemoveExportProcess
{
    private ProductRelationAttributeQueryInterface $productRelationAttributeQuery;

    private ProductRelationAttributeClient $relationAttributeClient;

    public function __construct(
        ProductRelationAttributeQueryInterface $productRelationAttributeQuery,
        ProductRelationAttributeClient $relationAttributeClient
    ) {
        $this->productRelationAttributeQuery = $productRelationAttributeQuery;
        $this->relationAttributeClient = $relationAttributeClient;
    }

    public function process(Export $export, Shopware6Channel $channel): void
    {
        $shopware6ProductCrossSellingIds = $this->productRelationAttributeQuery->getOthersAttributes(
            $channel->getId(),
            $channel->getProductRelationAttributes(),
        );

        foreach ($shopware6ProductCrossSellingIds as $crossSellingId) {
            $this->relationAttributeClient->delete($channel, $crossSellingId);
        }
    }
}
