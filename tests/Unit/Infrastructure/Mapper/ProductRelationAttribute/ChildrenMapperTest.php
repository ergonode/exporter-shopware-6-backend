<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\ProductRelationAttribute;

use Ergonode\ExporterShopware6\Domain\Repository\ProductRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttribute\ChildrenMapper;
use Ergonode\ExporterShopware6\Infrastructure\Model\ProductCrossSelling\AbstractAssignedProduct;
use Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\AbstractProductRelationAttributeCase;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\Value\Domain\ValueObject\StringCollectionValue;
use PHPUnit\Framework\MockObject\MockObject;

class ChildrenMapperTest extends AbstractProductRelationAttributeCase
{
    private const TEST_PRODUCT_ID = '0a6ef811-b1d7-4838-a435-bed88b81a951';
    private const SHOPWARE_ID = 'shopware_id';

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
        $this->product->method('hasAttribute')->willReturn(false);
        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );
        $new = $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);

        self::assertEmpty($new->getAssignedProducts());
    }

    public function testNoShopwareIdMapper(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(true);
        $this->product->expects(self::once())->method('getAttribute')
            ->willReturn($this->createMock(StringCollectionValue::class));

        $this->calculator->expects(self::once())->method('calculate')->willReturn([self::TEST_PRODUCT_ID]);

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $this->shopware6ProductRepository->method('load')->willReturn(null);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $new = $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);

        self::assertEmpty($new->getAssignedProducts());
    }

    public function testCorrectMapper(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(true);
        $this->product->expects(self::once())->method('getAttribute')
            ->willReturn($this->createMock(StringCollectionValue::class));

        $this->calculator->expects(self::once())->method('calculate')->willReturn([ self::TEST_PRODUCT_ID]);

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $this->shopware6ProductRepository->method('load')->willReturn(self::SHOPWARE_ID);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $new = $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
        self::assertIsArray($new->getAssignedProducts());
        self::assertNotEmpty($new->getAssignedProducts());
        self::assertEquals(self::SHOPWARE_ID, $new->getAssignedProducts()[0]->getProductId());
        self::assertEquals(1, $new->getAssignedProducts()[0]->getPosition());
    }

    public function testCorrectPositionMapper(): void
    {
        $this->product->expects(self::once())->method('hasAttribute')->willReturn(true);
        $this->product->expects(self::once())->method('getAttribute')
            ->willReturn($this->createMock(StringCollectionValue::class));

        $this->calculator->expects(self::once())->method('calculate')->willReturn([ self::TEST_PRODUCT_ID]);

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $this->shopware6ProductRepository->method('load')->willReturn(self::SHOPWARE_ID);

        $productCrossSelling = $this->getProductCrossSellingClass();
        $assigned = $this->createMock(AbstractAssignedProduct::class);
        $assigned->method('getPosition')->willReturn(10);
        $productCrossSelling->setAssignedProducts([$assigned]);

        $mapper = new ChildrenMapper(
            $this->calculator,
            $this->shopware6ProductRepository,
        );

        $new = $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
        self::assertEquals(self::SHOPWARE_ID, $new->getAssignedProducts()[1]->getProductId());
        self::assertEquals(11, $new->getAssignedProducts()[1]->getPosition());
    }
}
