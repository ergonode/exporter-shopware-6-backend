<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Domain\Command\Export;

use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductRelationAttributeExportCommand;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductRelationAttributeExportCommandTest extends TestCase
{
    /**
     * @var ExportLineId|MockObject
     */
    private ExportLineId $lineId;

    /**
     * @var ExportId|MockObject
     */
    private ExportId $exportId;

    /**
     * @var ProductId|MockObject
     */
    private ProductId $productId;

    /**
     * @var AttributeId|MockObject
     */
    private AttributeId $attributeId;

    protected function setUp(): void
    {
        $this->lineId = $this->createMock(ExportLineId::class);
        $this->exportId = $this->createMock(ExportId::class);
        $this->productId = $this->createMock(ProductId::class);
        $this->attributeId = $this->createMock(AttributeId::class);
    }

    public function testCreateCommand(): void
    {
        $command = new ProductRelationAttributeExportCommand(
            $this->lineId,
            $this->exportId,
            $this->productId,
            $this->attributeId,
        );

        self::assertEquals($this->lineId, $command->getLineId());
        self::assertEquals($this->exportId, $command->getExportId());
        self::assertEquals($this->productId, $command->getProductId());
        self::assertEquals($this->attributeId, $command->getAttributeId());
    }
}
