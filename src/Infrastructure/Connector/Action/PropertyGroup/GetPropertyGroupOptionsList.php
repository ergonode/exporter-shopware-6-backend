<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\PropertyGroup;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6PropertyGroupOption;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GetPropertyGroupOptionsList extends AbstractAction
{
    private const URI = '/api/property-group/%s/options?%s';

    private string $propertyGroupId;

    /**
     * @var array|array[]
     */
    private array $query;

    /**
     * @param array $query
     */
    public function __construct(string $propertyGroupId, array $query = [], int $limit = 500, int $page = null)
    {
        $this->propertyGroupId = $propertyGroupId;

        if ($limit > 0) {
            $query['limit'] = $limit;
        }
        if ($page > 0) {
            $query['page'] = $page;
        }

        $this->query = [
            'query' => $query,
        ];
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
     * @return Shopware6PropertyGroupOption[]
     */
    public function parseContent(?string $content): array
    {
        $result = [];
        $data = json_decode($content, true);

        foreach ($data['data'] as $datum) {
            $result[] = new Shopware6PropertyGroupOption(
                $datum['id'],
                $datum['attributes']['name'],
                $datum['attributes']['mediaId'],
                $datum['attributes']['position'],
            );
        }

        return $result;
    }

    private function getUri(): string
    {
        return rtrim(sprintf(self::URI, $this->propertyGroupId, http_build_query($this->query)), '?');
    }
}
