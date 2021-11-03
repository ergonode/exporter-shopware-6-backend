<?php

/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Model\VisibilitiesProduct;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GetProductProductVisibility extends AbstractAction
{
    private const URI = '/api/product/%s/visibilities';

    private string $productId;

    public function __construct(string $productId)
    {
        $this->productId = $productId;
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
     * @return VisibilitiesProduct[]|null
     *
     */
    public function parseContent(?string $content): ?array
    {
        $result = null;
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data['data'] ?? null)) {
            return null;
        }

        foreach ($data['data'] as $row) {
            $result[] = new VisibilitiesProduct(
                $row['id'] ?? null,
                $row['attributes']['productId'] ?? null,
                $row['attributes']['salesChannelId'] ?? null,
                $row['attributes']['visibility'] ?? null,
            );
        }

        return $result;
    }

    private function getUri(): string
    {
        return sprintf(self::URI, $this->productId);
    }
}
