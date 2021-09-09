<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Domain\Query;

use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;

interface ProductQueryInterface
{
    /**
     * @param ProductId[] $productIds
     *
     * @return string[]
     */
    public function findShopwareIdByProductIds(ChannelId $channelId, array $productIds): array;
}
