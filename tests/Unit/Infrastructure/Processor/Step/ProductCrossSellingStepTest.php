<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Processor\Step;

use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Step\ProductCrossSellingStep;
use Ergonode\Product\Domain\Query\ProductQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductCollectionId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductCrossSellingStepTest extends TestCase
{
    /**
     * @var ExportRepositoryInterface|MockObject
     */
    private ExportRepositoryInterface $exportRepository;

    /**
     * @var ProductQueryInterface|MockObject
     */
    private ProductQueryInterface $productQuery;

    private Shopware6Channel $channel;

    private ProductCrossSellingStep $step;

    protected function setUp(): void
    {
        $commandBus = $this->createMock(CommandBusInterface::class);
        $this->exportRepository = $this->createMock(ExportRepositoryInterface::class);
        $this->productQuery = $this->createMock(ProductQueryInterface::class);
        $this->channel = $this->createMock(Shopware6Channel::class);

        $this->step = new ProductCrossSellingStep($commandBus, $this->exportRepository, $this->productQuery);
    }

    public function testRelationAttributes(): void
    {
        $this->channel->method('getProductRelationAttributes')
            ->willReturn([$this->createMock(AttributeId::class)]);
        $this->productQuery->method('findProductIdByAttributeId')
            ->willReturn([$this->createMock(ProductId::class)]);

        $this->exportRepository->expects(self::once())->method('addLine');

        $exportId = $this->createMock(ExportId::class);

        $this->step->export($exportId, $this->channel);
    }

    public function testCrossSelling(): void
    {
        $this->channel->method('getProductRelationAttributes')
            ->willReturn([]);
        $this->channel->method('getCrossSelling')
            ->willReturn([$this->createMock(ProductCollectionId::class)]);

        $this->exportRepository->expects(self::once())->method('addLine');

        $exportId = $this->createMock(ExportId::class);

        $this->step->export($exportId, $this->channel);
    }
}
