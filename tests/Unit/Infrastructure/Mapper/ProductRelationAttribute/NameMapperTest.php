<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\ProductRelationAttribute;

use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttribute\NameMapper;
use Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\AbstractProductRelationAttributeCase;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;

class NameMapperTest extends AbstractProductRelationAttributeCase
{
    private const TEST_NAME = 'Name of Attribute';
    private const TEST_CODE = 'system_code';

    private ProductRelationAttribute $relationAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $code = $this->createMock(AttributeCode::class);
        $code->method('getValue')->willReturn(self::TEST_CODE);

        $this->relationAttribute = $this->createMock(ProductRelationAttribute::class);
        $this->relationAttribute->method('getCode')->willReturn($code);
    }

    public function testCorrectNameMapper(): void
    {
        $label = $this->createMock(TranslatableString::class);
        $label->method('get')->willReturn(self::TEST_NAME);
        $this->relationAttribute->method('getLabel')->willReturn($label);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $mapper = new NameMapper();

        $new = $mapper->map(
            $this->channel,
            $this->export,
            $productCrossSelling,
            $this->product,
            $this->relationAttribute,
        );

        self::assertEquals(self::TEST_NAME, $new->getName());
    }

    public function testCorrectNameFromCodeMapper(): void
    {
        $label = $this->createMock(TranslatableString::class);
        $label->method('get')->willReturn(null);
        $this->relationAttribute->method('getLabel')->willReturn($label);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $mapper = new NameMapper();

        $new = $mapper->map(
            $this->channel,
            $this->export,
            $productCrossSelling,
            $this->product,
            $this->relationAttribute,
        );

        self::assertEquals(self::TEST_CODE, $new->getName());
    }
}
