<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Step;

use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductCrossSellingExportCommand;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductRelationAttributeExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\ExportStepProcessInterface;
use Ergonode\Product\Domain\Query\ProductQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;

class ProductCrossSellingStep implements ExportStepProcessInterface
{
    private CommandBusInterface $commandBus;

    private ExportRepositoryInterface $exportRepository;

    private ProductQueryInterface $productQuery;

    public function __construct(
        CommandBusInterface $commandBus,
        ExportRepositoryInterface $exportRepository,
        ProductQueryInterface $productQuery
    ) {
        $this->commandBus = $commandBus;
        $this->exportRepository = $exportRepository;
        $this->productQuery = $productQuery;
    }

    public function export(ExportId $exportId, Shopware6Channel $channel): void
    {
        if (!empty($channel->getProductRelationAttributes())) {
            $this->productRelationAttribute($channel, $exportId);

            return;
        }

        $this->productCrossSelling($channel, $exportId);
    }

    private function productCrossSelling(Shopware6Channel $channel, ExportId $exportId): void
    {
        $crossSellList = $channel->getCrossSelling();
        foreach ($crossSellList as $productCollectionId) {
            $lineId = ExportLineId::generate();
            $processCommand = new  ProductCrossSellingExportCommand($lineId, $exportId, $productCollectionId);
            $this->exportRepository->addLine($lineId, $exportId, $productCollectionId);
            $this->commandBus->dispatch($processCommand, true);
        }
    }

    private function productRelationAttribute(Shopware6Channel $channel, ExportId $exportId): void
    {
        $productRelationAttributes = $channel->getProductRelationAttributes();
        foreach ($productRelationAttributes as $relationAttribute) {
            $productIds = $this->productQuery->findProductIdByAttributeId($relationAttribute);
            foreach ($productIds as $productId) {
                $lineId = ExportLineId::generate();
                $processCommand = new  ProductRelationAttributeExportCommand(
                    $lineId,
                    $exportId,
                    $productId,
                    $relationAttribute,
                );
                $this->exportRepository->addLine($lineId, $exportId, $productId);
                $this->commandBus->dispatch($processCommand, true);
            }
        }
    }
}
