<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class HasMedia extends AbstractAction
{
    private const URI = '/api/media/%s';

    private string $mediaId;

    public function __construct(string $mediaId)
    {
        $this->mediaId = $mediaId;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_GET,
            $this->getUri(),
            $this->buildHeaders(),
        );
    }

    /**
     * @throws \JsonException
     */
    public function parseContent(?string $content): ?string
    {
        if (null === $content) {
            return null;
        }

        return $this->mediaId;
    }

    private function getUri(): string
    {
        return sprintf(self::URI, $this->mediaId);
    }
}
