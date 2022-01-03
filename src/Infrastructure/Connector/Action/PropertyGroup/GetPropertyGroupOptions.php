<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\PropertyGroup;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6PropertyGroupOption;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GetPropertyGroupOptions extends AbstractAction
{
    private const URI = '/api/property-group/%s/options';

    private string $propertyGroupId;

    public function __construct(string $propertyGroupId)
    {
        $this->propertyGroupId = $propertyGroupId;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_GET,
            $this->getUri(),
            $this->buildHeaders()
        );
    }

    /**
     * @throws \JsonException
     */
    public function parseContent(?string $content): ?array
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $result = [];
        foreach ($data['data'] as $row) {
            $result[$row['id']] = new Shopware6PropertyGroupOption(
                $row['id'],
                $row['attributes']['name'],
                $row['attributes']['mediaId'],
                $row['attributes']['position'],
                $row['attributes']['groupId'] ?? null
            );
        }

        foreach ($data['included'] as $included) {
            $propertyGroupOptionId = $included['attributes']['groupId'];
            if (isset($result[$propertyGroupOptionId])) {
                $propertyGroup = $result[$propertyGroupOptionId];

                $propertyGroup->addTranslations($included['languageId'], 'name', $included['name']);
            }
        }

        return $result;
    }

    private function getUri(): string
    {
        return sprintf(self::URI, $this->propertyGroupId);
    }
}
