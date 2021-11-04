<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Product\Options;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class DeleteOptions extends AbstractAction
{
    private const URI = '/api/product/%s/options/%s';

    private string $productId;

    private string $optionId;

    public function __construct(string $productId, string $optionId)
    {
        $this->productId = $productId;
        $this->optionId = $optionId;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_DELETE,
            $this->getUri(),
            $this->buildHeaders(),
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
        return sprintf(self::URI, $this->productId, $this->optionId);
    }
}
