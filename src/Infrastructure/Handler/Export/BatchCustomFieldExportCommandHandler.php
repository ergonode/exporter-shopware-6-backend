<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Handler\Export;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Command\Export\BatchCustomFieldExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6CustomFieldClient;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\CustomFieldShopware6ExportProcess;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Webmozart\Assert\Assert;

class BatchCustomFieldExportCommandHandler
{
    protected Shopware6CustomFieldClient $client;

    private ExportRepositoryInterface $exportRepository;

    private ChannelRepositoryInterface $channelRepository;

    private CustomFieldShopware6ExportProcess $process;

    public function __construct(
        ExportRepositoryInterface $exportRepository,
        ChannelRepositoryInterface $channelRepository,
        CustomFieldShopware6ExportProcess $process,
        Shopware6CustomFieldClient $client
    ) {
        $this->exportRepository = $exportRepository;
        $this->channelRepository = $channelRepository;
        $this->process = $process;
        $this->client = $client;
    }

    public function __invoke(BatchCustomFieldExportCommand $command): void
    {
        $export = $this->exportRepository->load($command->getExportId());
        Assert::isInstanceOf($export, Export::class);
        $channel = $this->channelRepository->load($export->getChannelId());
        Assert::isInstanceOf($channel, Shopware6Channel::class);
        $attributeIds = $command->getAttributeIds();
        Assert::allIsInstanceOf($attributeIds, AttributeId::class);

        $this->process->process($export, $channel, $attributeIds);
    }
}
