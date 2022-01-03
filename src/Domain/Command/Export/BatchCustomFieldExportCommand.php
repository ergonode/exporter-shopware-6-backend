<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Domain\Command\Export;

use Ergonode\Channel\Domain\Command\ExporterCommandInterface;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ExportId;

class BatchCustomFieldExportCommand implements ExporterCommandInterface
{
    private ExportId $exportId;

    /**
     * @var AttributeId[]
     */
    private array $attributeIds;

    public function __construct(ExportId $exportId, array $attributeIds)
    {
        $this->exportId = $exportId;
        $this->attributeIds = $attributeIds;
    }

    public function getExportId(): ExportId
    {
        return $this->exportId;
    }

    public function getAttributeIds(): array
    {
        return $this->attributeIds;
    }
}
