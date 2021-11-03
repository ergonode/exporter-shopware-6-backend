<?php

/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Step;

use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductVisibilityCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\ExportStepProcessInterface;
use Ergonode\Product\Domain\Entity\SimpleProduct;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Ergonode\Product\Domain\Query\ProductQueryInterface;
use Ergonode\Segment\Domain\Query\SegmentProductsQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;

class ProductVisibilityStep implements ExportStepProcessInterface
{
    private ProductQueryInterface $productQuery;

    private SegmentProductsQueryInterface $segmentProductsQuery;

    private CommandBusInterface $commandBus;

    private ExportRepositoryInterface $exportRepository;

    public function __construct(
        ProductQueryInterface $productQuery,
        SegmentProductsQueryInterface $segmentProductsQuery,
        CommandBusInterface $commandBus,
        ExportRepositoryInterface $exportRepository
    ) {
        $this->productQuery = $productQuery;
        $this->segmentProductsQuery = $segmentProductsQuery;
        $this->commandBus = $commandBus;
        $this->exportRepository = $exportRepository;
    }

    public function export(ExportId $exportId, Shopware6Channel $channel): void
    {
        $variableProductList = $this->getProduct($channel, VariableProduct::TYPE);
        foreach ($variableProductList as $product) {
            $productId = new ProductId($product);
            $this->sendCommandVisibility($exportId, $productId);
        }

        $simpleProductList = $this->getProduct($channel, SimpleProduct::TYPE);
        foreach ($simpleProductList as $product) {
            $productId = new ProductId($product);
            $this->sendCommandVisibility($exportId, $productId);
        }
    }

    private function sendCommandVisibility(ExportId $exportId, ProductId $productId): void
    {
        $lineId = ExportLineId::generate();

        $processCommand = new ProductVisibilityCommand($lineId, $exportId, $productId);
        $this->exportRepository->addLine($lineId, $exportId, $productId);
        $this->commandBus->dispatch($processCommand, true);
    }

    /**
     * @return string[]
     */
    private function getProduct(Shopware6Channel $channel, string $type): array
    {
        if ($channel->getSegment()) {
            return $this->segmentProductsQuery->getProductsByType($channel->getSegment(), $type);
        }

        return $this->productQuery->findProductIdByType($type);
    }
}
