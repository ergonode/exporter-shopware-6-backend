<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model;

use Webmozart\Assert\Assert;

class BatchVisibilitiesProduct implements \JsonSerializable
{
    private const REQUEST_NAME = 'write-product-visibility';
    public const ACTION_DELETE = 'delete';
    public const ACTION_UPSERT = 'upsert';
    private const ACTIONS = [self::ACTION_DELETE, self::ACTION_UPSERT];

    /**
     * @var VisibilitiesProduct[]
     */
    protected array $visibilities;

    protected string $action;

    public function __construct(array $visibilities, string $action)
    {
        Assert::allIsInstanceOf($visibilities, VisibilitiesProduct::class);
        Assert::inArray($action, self::ACTIONS);
        $this->visibilities = $visibilities;
        $this->action = $action;
    }

    public function getVisibilities(): array
    {
        return $this->visibilities;
    }

    public function jsonSerialize(): array
    {
        $result[self::REQUEST_NAME] =
            [
                "entity"  => "product_visibility",
                "action"  => $this->action,
                "payload" => [],
            ];
        foreach ($this->getVisibilities() as $visibility) {
            $result[self::REQUEST_NAME]['payload'][] = $visibility->jsonSerialize();
        }

        return $result;
    }
}
