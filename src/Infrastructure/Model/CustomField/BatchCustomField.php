<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model\CustomField;

use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractShopware6CustomField;
use Webmozart\Assert\Assert;

class BatchCustomField implements \JsonSerializable
{
    /**
     * @var AbstractShopware6CustomField[]
     */
    protected array $customFields;

    public function __construct(array $customFields)
    {
        Assert::allIsInstanceOf($customFields, AbstractShopware6CustomField::class);
        $this->customFields = $customFields;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->getCustomFields() as $customField) {
            $result[$customField->getRequestName()] =
                [
                    "entity"  => "custom_field",
                    "action"  => "upsert",
                    "payload" => [$customField->jsonSerialize()],
                ];
        }

        return $result;
    }
}
