<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Handler\Export;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductRelationAttributeExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Handler\Export\ProductRelationAttributeExportCommandHandler;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\ProductRelationAttributeExportProcess;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\ProductRelationAttributeRemoveElementExportProcess;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductRelationAttributeExportCommandHandlerTest extends TestCase
{
    /**
     * @var ExportRepositoryInterface|MockObject
     */
    private ExportRepositoryInterface $exportRepository;

    /**
     * @var ChannelRepositoryInterface|MockObject
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var ProductRelationAttributeRemoveElementExportProcess|MockObject
     */
    private ProductRelationAttributeRemoveElementExportProcess $removeProcess;
    /**
     * @var ProductRelationAttributeExportProcess|MockObject
     */
    private ProductRelationAttributeExportProcess $process;

    protected function setUp(): void
    {
        $this->exportRepository = $this->createMock(ExportRepositoryInterface::class);
        $this->exportRepository->method('load')
            ->willReturn($this->createMock(Export::class));
        $this->exportRepository->expects(self::once())->method('load');

        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->channelRepository->method('load')
            ->willReturn($this->createMock(Shopware6Channel::class));
        $this->channelRepository->expects(self::once())->method('load');

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepository->method('load')
            ->willReturn($this->createMock(AbstractProduct::class));
        $this->productRepository->expects(self::once())->method('load');

        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->attributeRepository->method('load')
            ->willReturn($this->createMock(ProductRelationAttribute::class));
        $this->attributeRepository->expects(self::once())->method('load');

        $this->removeProcess = $this->createMock(ProductRelationAttributeRemoveElementExportProcess::class);
        $this->removeProcess->expects(self::once())->method('process');


        $this->process = $this->createMock(ProductRelationAttributeExportProcess::class);
        $this->process->expects(self::once())->method('process');
    }

    public function testHandling(): void
    {
        $command = $this->createMock(ProductRelationAttributeExportCommand::class);

        $handler = new ProductRelationAttributeExportCommandHandler(
            $this->exportRepository,
            $this->channelRepository,
            $this->productRepository,
            $this->attributeRepository,
            $this->removeProcess,
            $this->process,
        );
        $handler->__invoke($command);
    }
}
