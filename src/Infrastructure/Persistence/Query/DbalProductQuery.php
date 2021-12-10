<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Persistence\Query;

use Doctrine\DBAL\Connection;
use Ergonode\ExporterShopware6\Domain\Query\ProductQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;

class DbalProductQuery implements ProductQueryInterface
{
    private const TABLE = 'exporter.shopware6_product';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findShopwareIdByProductIds(ChannelId $channelId, array $productIds): array
    {
        $query = $this->connection->createQueryBuilder();

        return $query
            ->select('p.shopware6_id')
            ->from(self::TABLE, 'p')
            ->where($query->expr()->eq('p.channel_id', ':channelId'))
            ->setParameter(':channelId', $channelId->getValue())
            ->andWhere($query->expr()->in('p.product_id', ':ids'))
            ->setParameter(':ids', $productIds, Connection::PARAM_STR_ARRAY)
            ->execute()->fetchFirstColumn();
    }
}
