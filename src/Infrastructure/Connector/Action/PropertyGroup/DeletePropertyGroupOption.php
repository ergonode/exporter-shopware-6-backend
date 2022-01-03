<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\PropertyGroup;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class DeletePropertyGroupOption extends AbstractAction
{
    private const URI = '/api/property-group-option/%s';

    private string $propertyGroupOptionId;

    public function __construct(string $propertyGroupOptionId)
    {
        $this->propertyGroupOptionId = $propertyGroupOptionId;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_DELETE,
            $this->getUri(),
            $this->buildHeaders()
        );
    }

    /**
     * @return null
     */
    public function parseContent(?string $content)
    {
        return null;
    }

    private function getUri(): string
    {
        return sprintf(self::URI, $this->propertyGroupOptionId);
    }
}
