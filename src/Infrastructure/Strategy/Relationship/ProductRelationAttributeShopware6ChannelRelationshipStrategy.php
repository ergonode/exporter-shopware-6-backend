<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Strategy\Relationship;

use Ergonode\Channel\Domain\Query\ChannelQueryInterface;
use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Core\Infrastructure\Model\RelationshipGroup;
use Ergonode\Core\Infrastructure\Strategy\RelationshipStrategyInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\AggregateId;
use Webmozart\Assert\Assert;

class ProductRelationAttributeShopware6ChannelRelationshipStrategy  implements RelationshipStrategyInterface
{
    private const MESSAGE = 'Object has active relationships with channel %relations%';

    private ChannelQueryInterface $query;

    private ChannelRepositoryInterface $repository;

    public function __construct(ChannelQueryInterface $query, ChannelRepositoryInterface $repository)
    {
        $this->query = $query;
        $this->repository = $repository;
    }

    public function supports(AggregateId $id): bool
    {
        return $id instanceof AttributeId;
    }

    public function getRelationshipGroup(AggregateId $id): RelationshipGroup
    {
        Assert::isInstanceOf($id, AttributeId::class);
        $channelIds = $this->query->findChannelIdsByType(Shopware6Channel::TYPE);

        $relation = [];
        foreach ($channelIds as $channelId) {
            $channel = $this->repository->load($channelId);
            if ($channel instanceof Shopware6Channel) {
                foreach ($channel->getProductRelationAttributes() as $relationAttributeId) {
                    if ($relationAttributeId->isEqual($id)) {
                        $relation[] = $channelId;
                    }
                }
            }
        }

        return new RelationshipGroup(self::MESSAGE, $relation);
    }
}
