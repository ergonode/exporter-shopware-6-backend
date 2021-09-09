<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Persistence\Query;

use Doctrine\DBAL\Connection;
use Ergonode\ExporterShopware6\Domain\Query\ProductRelationAttributeQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;

class DbalProductRelationAttributeQuery implements ProductRelationAttributeQueryInterface
{
    private const TABLE = 'exporter.shopware6_product_relation_attribute';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getOthersAttributes(ChannelId $channelId, array $attributeIds): array
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->select('DISTINCT pra.shopware6_id')
            ->from(self::TABLE, 'pra')
            ->where($query->expr()->eq('pra.channel_id', ':channelId'))
            ->setParameter(':channelId', $channelId->getValue());

        if ($attributeIds) {
            $query->andWhere($query->expr()->notIn('pra.attribute_id', ':attributeId'))
                ->setParameter(':attributeId', $attributeIds, Connection::PARAM_STR_ARRAY);
        }

        return $query->execute()->fetchFirstColumn();
    }
}
