<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Handler\Export;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Command\Export\ProductRelationAttributeExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\ProductRelationAttributeExportProcess;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\ProductRelationAttributeRemoveElementExportProcess;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\Product\Domain\Repository\ProductRepositoryInterface;
use Webmozart\Assert\Assert;

class ProductRelationAttributeExportCommandHandler
{
    private ExportRepositoryInterface $exportRepository;

    private ChannelRepositoryInterface $channelRepository;

    private ProductRepositoryInterface $productRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private ProductRelationAttributeRemoveElementExportProcess $removeProcess;

    private ProductRelationAttributeExportProcess $process;

    public function __construct(
        ExportRepositoryInterface $exportRepository,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductRelationAttributeRemoveElementExportProcess $removeProcess,
        ProductRelationAttributeExportProcess $process
    ) {
        $this->exportRepository = $exportRepository;
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->removeProcess = $removeProcess;
        $this->process = $process;
    }

    public function __invoke(ProductRelationAttributeExportCommand $command): void
    {
        $export = $this->exportRepository->load($command->getExportId());
        Assert::isInstanceOf($export, Export::class);
        $channel = $this->channelRepository->load($export->getChannelId());
        Assert::isInstanceOf($channel, Shopware6Channel::class);
        $product = $this->productRepository->load($command->getProductId());
        Assert::isInstanceOf($product, AbstractProduct::class);
        $attribute = $this->attributeRepository->load($command->getAttributeId());
        Assert::isInstanceOf($attribute, ProductRelationAttribute::class);

        $this->removeProcess->process($command->getLineId(), $export, $channel, $product, $attribute);
        $this->process->process($command->getLineId(), $export, $channel, $product, $attribute);
    }
}
