<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\ProductRelationAttribute;

use Ergonode\ExporterShopware6\Domain\Repository\ProductRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Mapper\Shopware6ExporterProductNoFoundException;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductRelationAttribute\RootProductIdMapper;
use Ergonode\ExporterShopware6\Tests\Unit\Infrastructure\Mapper\AbstractProductRelationAttributeCase;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Domain\Query\ProductQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class RootProductIdMapperTest extends AbstractProductRelationAttributeCase
{
    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $shopware6ProductRepository;
    /**
     * @var ProductQueryInterface|MockObject
     */
    private ProductQueryInterface $productQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shopware6ProductRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productQuery = $this->createMock(ProductQueryInterface::class);
    }

    public function testCorrectMapper(): void
    {
        $this->shopware6ProductRepository->method('load')->willReturn('sh_id');

        $productCrossSelling = $this->getProductCrossSellingClass();

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $mapper = new RootProductIdMapper(
            $this->shopware6ProductRepository,
            $this->productQuery,
        );
        $new = $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);

        self::assertEquals('sh_id', $new->getProductId());
    }

    public function testNoShopwareIdMapper(): void
    {
        $this->expectException(Shopware6ExporterProductNoFoundException::class);

        $productCrossSelling = $this->getProductCrossSellingClass();

        $relationAttribute = $this->createMock(ProductRelationAttribute::class);

        $mapper = new RootProductIdMapper(
            $this->shopware6ProductRepository,
            $this->productQuery,
        );
        $mapper->map($this->channel, $this->export, $productCrossSelling, $this->product, $relationAttribute);
    }
}
