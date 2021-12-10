<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Persistence\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ergonode\ExporterShopware6\Domain\Repository\ProductRelationAttributeRepositoryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;

class DbalProductRelationAttributeRepository implements ProductRelationAttributeRepositoryInterface
{
    private const TABLE = 'exporter.shopware6_product_relation_attribute';
    private const FIELDS = [
        'channel_id',
        'product_id',
        'attribute_id',
        'shopware6_id',
    ];

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function load(ChannelId $channelId, ProductId $productId, AttributeId $relationAttributeId): ?string
    {
        $query = $this->connection->createQueryBuilder();
        $record = $query
            ->select('pra.shopware6_id')
            ->from(self::TABLE, 'pra')
            ->where($query->expr()->eq('pra.channel_id', ':channelId'))
            ->setParameter(':channelId', $channelId->getValue())
            ->andWhere($query->expr()->eq('pra.product_id', ':productId'))
            ->setParameter(':productId', $productId->getValue())
            ->andWhere($query->expr()->eq('pra.attribute_id', ':attributeId'))
            ->setParameter(':attributeId', $relationAttributeId->getValue())
            ->execute()
            ->fetchOne();

        if ($record) {
            return (string) $record;
        }

        return null;
    }

    public function save(
        ChannelId $channelId,
        ProductId $productId,
        AttributeId $relationAttributeId,
        string $shopwareId
    ): void {
        $sql = 'INSERT INTO ' . self::TABLE . ' (channel_id, product_id, attribute_id, shopware6_id, updated_at) 
        VALUES (:channelId, :productId, :attributeId, :shopware6Id, :updatedAt)
            ON CONFLICT ON CONSTRAINT shopware6_product_relation_attribute_pkey
                DO UPDATE SET shopware6_id = :shopware6Id, updated_at = :updatedAt
        ';

        $this->connection->executeQuery(
            $sql,
            [
                'channelId' => $channelId->getValue(),
                'productId' => $productId->getValue(),
                'attributeId' => $relationAttributeId->getValue(),
                'shopware6Id' => $shopwareId,
                'updatedAt' => new \DateTimeImmutable(),
            ],
            [
                'updatedAt' => Types::DATETIMETZ_MUTABLE,
            ],
        );
    }

    public function exists(ChannelId $channelId, ProductId $productId, AttributeId $relationAttributeId): bool
    {
        $query = $this->connection->createQueryBuilder();
        $result = $query->select(1)
            ->from(self::TABLE, 'pra')
            ->where($query->expr()->eq('pra.channel_id', ':channelId'))
            ->setParameter(':channelId', $channelId->getValue())
            ->andWhere($query->expr()->eq('pra.product_id', ':productId'))
            ->setParameter(':productId', $productId->getValue())
            ->andWhere($query->expr()->eq('pra.attribute_id', ':attributeId'))
            ->setParameter(':attributeId', $relationAttributeId->getValue())
            ->execute()
            ->rowCount();

        if ($result) {
            return true;
        }
        return false;
    }

    public function delete(ChannelId $channelId, string $shopwareId): void
    {
        $this->connection->delete(
            self::TABLE,
            [
                'shopware6_id' => $shopwareId,
                'channel_id' => $channelId->getValue(),
            ],
        );
    }
}
