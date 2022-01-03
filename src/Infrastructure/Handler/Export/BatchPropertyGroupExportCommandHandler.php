<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Handler\Export;

use Ergonode\Channel\Domain\Repository\ChannelRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Command\Export\BatchPropertyGroupExportCommand;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\Process\PropertyGroupShopware6ExportProcess;

class BatchPropertyGroupExportCommandHandler extends AbstractBatchExportCommandHandler
{
    private PropertyGroupShopware6ExportProcess $process;

    public function __construct(
        ExportRepositoryInterface $exportRepository,
        ChannelRepositoryInterface $channelRepository,
        PropertyGroupShopware6ExportProcess $process
    ) {
        parent::__construct($exportRepository, $channelRepository);
        $this->process = $process;
    }

    public function __invoke(BatchPropertyGroupExportCommand $command): void
    {
        $this->validateAndProcessCommand($command);
    }

    protected function processCommand(Export $export, Shopware6Channel $channel, array $attributeIds): void
    {
        $this->process->process($export, $channel, $attributeIds);
    }
}
