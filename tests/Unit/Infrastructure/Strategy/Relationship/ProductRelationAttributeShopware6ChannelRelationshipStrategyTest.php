<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Strategy\Relationship;

use Ergonode\Channel\Domain\Query\ChannelQueryInterface;
use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
// phpcs:ignore
use Ergonode\ExporterShopware6\Infrastructure\Strategy\Relationship\ProductRelationAttributeShopware6ChannelRelationshipStrategy;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;
use Ergonode\SharedKernel\Domain\AggregateId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductRelationAttributeShopware6ChannelRelationshipStrategyTest extends TestCase
{
    /**
     * @var ChannelQueryInterface|MockObject
     */
    private ChannelQueryInterface $query;

    /**
     * @var ChannelRepositoryInterface|MockObject
     */
    private ChannelRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->query = $this->createMock(ChannelQueryInterface::class);
        $this->repository = $this->createMock(ChannelRepositoryInterface::class);
    }

    public function testIsSupported(): void
    {
        $validId = $this->createMock(AttributeId::class);
        $inValidId = $this->createMock(AggregateId::class);

        $strategy = new ProductRelationAttributeShopware6ChannelRelationshipStrategy($this->query, $this->repository);

        self::assertTrue($strategy->supports($validId));
        self::assertFalse($strategy->supports($inValidId));
    }

    public function testRelations(): void
    {
        $id = $this->createMock(AttributeId::class);
        $id->method('isEqual')->willReturn(true);
        $relationId = [$this->createMock(ChannelId::class)];

        $this->query->expects(self::once())->method('findChannelIdsByType')->willReturn($relationId);

        $channel = $this->createMock(Shopware6Channel::class);
        $channel->method('getProductRelationAttributes')->willReturn([$id]);
        $this->repository->method('load')->willReturn($channel);

        $strategy = new ProductRelationAttributeShopware6ChannelRelationshipStrategy($this->query, $this->repository);
        $result = $strategy->getRelationshipGroup($id);

        self::assertSame($relationId, $result->getRelations());
    }
}
