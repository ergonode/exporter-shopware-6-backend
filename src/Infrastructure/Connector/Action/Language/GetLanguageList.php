<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Language;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6QueryBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GetLanguageList extends AbstractAction
{
    private const URI = '/api/language?%s';

    private Shopware6QueryBuilder $query;

    public function __construct(Shopware6QueryBuilder $query)
    {
        $this->query = $query;
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
     * @return Shopware6Language[]
     * @throws \JsonException
     */
    public function parseContent(?string $content): array
    {
        $result = [];
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $locales = [];

        foreach ($data['included'] as $includedAssociation) {
            if ($includedAssociation['type'] === 'locale'
                && isset($includedAssociation['id'], $includedAssociation['attributes']['code'])
                && false === isset($locales[$includedAssociation['id']])
            ) {
                $locales[$includedAssociation['id']] = str_replace(
                    '-',
                    '_',
                    $includedAssociation['attributes']['code']
                );
            }
        }

        foreach ($data['data'] as $row) {
            $result[$row['id']] = new Shopware6Language(
                $row['id'],
                $row['attributes']['name'],
                $row['attributes']['localeId'],
                $row['attributes']['translationCodeId'],
                $locales[$row['attributes']['localeId']] ?? null
            );
        }

        return $result;
    }

    private function getUri(): string
    {
        return rtrim(sprintf(self::URI, $this->query->getQuery()), '?');
    }
}
