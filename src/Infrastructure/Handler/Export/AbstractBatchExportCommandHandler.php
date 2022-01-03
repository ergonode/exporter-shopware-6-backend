<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Handler\Export;

use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Command\Export\AbstractBatchExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Webmozart\Assert\Assert;

abstract class AbstractBatchExportCommandHandler
{
    private ExportRepositoryInterface $exportRepository;

    private ChannelRepositoryInterface $channelRepository;

    public function __construct(
        ExportRepositoryInterface $exportRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->exportRepository = $exportRepository;
        $this->channelRepository = $channelRepository;
    }
    public function validateAndProcessCommand(AbstractBatchExportCommand $command): void
    {
        $export = $this->exportRepository->load($command->getExportId());
        Assert::isInstanceOf($export, Export::class);
        $channel = $this->channelRepository->load($export->getChannelId());
        Assert::isInstanceOf($channel, Shopware6Channel::class);
        $attributeIds = $command->getAttributeIds();
        Assert::allIsInstanceOf($attributeIds, AttributeId::class);

        $this->processCommand($export, $channel, $attributeIds);
    }

    abstract protected function processCommand(
        Export $export,
        Shopware6Channel $channel,
        array $attributeIds
    );
}
