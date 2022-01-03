<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model\PropertyGroupOption;

use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6PropertyGroupOption;
use Webmozart\Assert\Assert;

class BatchPropertyGroupOption implements \JsonSerializable
{
    /**
     * @var Shopware6PropertyGroupOption[]
     */
    protected array $options;

    public function __construct(array $options)
    {
        Assert::allIsInstanceOf($options, Shopware6PropertyGroupOption::class);
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->getOptions() as $option) {
            $result[$option->getRequestName()] =
                [
                    "entity"  => "property_group_option",
                    "action"  => "upsert",
                    "payload" => [$option->jsonSerialize()],
                ];
        }

        return $result;
    }
}
