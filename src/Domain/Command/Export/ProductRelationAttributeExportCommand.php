<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Domain\Command\Export;

use Ergonode\Channel\Domain\Command\ExporterCommandInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;

class ProductRelationAttributeExportCommand implements ExporterCommandInterface
{
    private ExportLineId $lineId;

    private ExportId $exportId;

    private ProductId $productId;

    private AttributeId $attributeId;

    public function __construct(
        ExportLineId $lineId,
        ExportId $exportId,
        ProductId $productId,
        AttributeId $attributeId
    ) {
        $this->lineId = $lineId;
        $this->exportId = $exportId;
        $this->productId = $productId;
        $this->attributeId = $attributeId;
    }

    public function getLineId(): ExportLineId
    {
        return $this->lineId;
    }

    public function getExportId(): ExportId
    {
        return $this->exportId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getAttributeId(): AttributeId
    {
        return $this->attributeId;
    }
}
