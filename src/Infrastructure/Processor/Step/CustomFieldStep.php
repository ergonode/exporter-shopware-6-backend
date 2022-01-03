<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Step;

use Ergonode\ExporterShopware6\Domain\Command\Export\BatchCustomFieldExportCommand;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Processor\ExportStepProcessInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;

class CustomFieldStep implements ExportStepProcessInterface
{
    private CommandBusInterface $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function export(ExportId $exportId, Shopware6Channel $channel): void
    {
        $processCommand = new BatchCustomFieldExportCommand($exportId, $channel->getCustomField());
        $this->commandBus->dispatch($processCommand, true);
    }
}
