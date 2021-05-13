<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Infrastructure\Mapper\ProductRelationAttribute;

use Ergonode\ExporterShopware6\Domain\Repository\ProductRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttribute\ChildrenMapper;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\ExporterShopware6\Infrastructure\Model\ProductCrossSelling\AbstractAssignedProduct;
use Ergonode\ExporterShopware6\Tests\Infrastructure\Mapper\AbstractProductRelationAttributeCase;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\Value\Domain\ValueObject\StringCollectionValue;
use PHPUnit\Framework\MockObject\MockObject;

class ChildrenMapperTest extends AbstractProductRelationAttributeCase
{
    private const TEST_PRODUCT_ID = '0a6ef811-b1d7-4838-a435-bed88b81a951';
    
    /**
     * @var TranslationInheritanceCalculator|MockObject
     */
    private TranslationInheritanceCalculator $calculator;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $shopware6ProductRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = $this->createMock(TranslationInheritanceCalculator::class);
        $this->shopware6ProductRepository = $this->createMock(ProductRepositoryInterface::class);
    }

    public function testNoAttribute(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(false);
        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $productCrossSelling = $this->createMock(AbstractProductCrossSelling::class);
        $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
    }

    public function testNoShopwareIdMapper(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(true);
        $this->product->expects(self::once())->method('getAttribute')
            ->willReturn($this->createMock(StringCollectionValue::class));

        $this->calculator->expects(self::once())->method('calculate')->willReturn([self::TEST_PRODUCT_ID]);

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $assigned = $this->createMock(AbstractAssignedProduct::class);
        $assigned->method('getPosition')->willReturn(2);

        $productCrossSelling = $this->createMock(AbstractProductCrossSelling::class);
        $productCrossSelling->method('getAssignedProducts')->willReturn([$assigned]);

        $this->shopware6ProductRepository->method('load')->willReturn(null);

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
    }

    public function testCorrectMapper(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(true);
        $this->product->expects(self::once())->method('getAttribute')
            ->willReturn($this->createMock(StringCollectionValue::class));

        $this->calculator->expects(self::once())->method('calculate')->willReturn([ self::TEST_PRODUCT_ID]);

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $assigned = $this->createMock(AbstractAssignedProduct::class);
        $assigned->method('getPosition')->willReturn(2);

        $productCrossSelling = $this->createMock(AbstractProductCrossSelling::class);
        $productCrossSelling->method('getAssignedProducts')->willReturn([$assigned]);

       $this->shopware6ProductRepository->method('load')->willReturn('sh_id');

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
    }
}
