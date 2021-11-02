<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Currency;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PostCurrencyCreate extends AbstractAction
{
    private const URI = '/api/currency?%s';

    protected bool $response;

    private string $iso;

    public function __construct(string $iso, bool $response = false)
    {
        $this->iso = $iso;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_POST,
            $this->getUri(),
            $this->buildHeaders(),
            $this->buildBody()
        );
    }

    /**
     * @param string|null $content
     *
     * @return string|null
     *
     * @throws \JsonException
     */
    public function parseContent(?string $content)
    {
        if (!$content) {
            return null;
        }

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $data['data']['id'] ?? null;
    }

    private function buildBody(): string
    {
        $body = [
            'factor' => 1,
            'symbol' => $this->iso,
            'isoCode' => $this->iso,
            'shortName' => $this->iso,
            'name' => $this->iso,
            'itemRounding' => [
                'interval' => 0.01,
                'decimals' => 2,
                'roundForNet' => true
            ],
            'totalRounding' => [
                'interval' => 0.01,
                'decimals' => 2,
                'roundForNet' => true
            ],
        ];

        return json_encode($body);
    }

    private function getUri(): string
    {
        $query = [];
        if ($this->response) {
            $query['_response'] = 'true';
        }

        return rtrim(sprintf(self::URI, http_build_query($query)), '?');
    }
}
