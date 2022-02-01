<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Product;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6QueryBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductCategory;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductConfiguratorSettings;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductMedia;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductPrice;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductTranslation;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GetProductList extends AbstractAction
{
    private const URI = '/api/product?%s';

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
     * @param string|null $content
     * @return Shopware6Product[]
     *
     * @throws JsonException
     */
    public function parseContent(?string $content): array
    {
        $result = [];
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $includedMedia = [];
        $includedConfiguratorSettings = [];
        $includedTranslations = [];
        foreach ($data['included'] as $includedAssociation) {
            if (false === isset($includedAssociation['id'])) {
                continue;
            }

            $id = $includedAssociation['id'];
            $type = $includedAssociation['type'];
            $attributes = $includedAssociation['attributes'];

            if ($type === 'product_media') {
                $includedMedia[$id] = new Shopware6ProductMedia(
                    $id,
                    $attributes['mediaId'] ?? null,
                    $attributes['position'] ?? null,
                );
            } elseif ($type === 'product_configurator_setting') {
                $includedConfiguratorSettings[$id] = new Shopware6ProductConfiguratorSettings(
                    $id,
                    $attributes['optionId'] ?? null
                );
            } elseif ($type === 'product_translation') {
                $includedTranslations[$id] = new Shopware6ProductTranslation(
                    $id,
                    $attributes['metaDescription'],
                    $attributes['name'],
                    $attributes['keywords'],
                    $attributes['description'],
                    $attributes['metaTitle'],
                    $attributes['packUnit'],
                    $attributes['packUnitPlural'],
                    $attributes['customSearchKeywords'],
                    $attributes['slotConfig'],
                    $attributes['customFields'],
                    $attributes['createdAt'],
                    $attributes['updatedAt'],
                    $attributes['productId'],
                    $attributes['languageId'],
                    $attributes['productVersionId'],
                    $attributes['apiAlias'],
                );
            }
        }

        if (count($data['data']) > 0) {
            foreach ($data['data'] as $row) {
                $properties = null;
                $options = null;
                $price = null;

                if ($row['attributes']['price']) {
                    foreach ($row['attributes']['price'] as $attributePrice) {
                        $price[] = new Shopware6ProductPrice(
                            $attributePrice['currencyId'],
                            $attributePrice['net'],
                            $attributePrice['gross'],
                            $attributePrice['linked']
                        );
                    }
                }

                if ($row['attributes']['propertyIds']) {
                    foreach ($row['attributes']['propertyIds'] as $propertyId) {
                        $properties[] = [
                            'id' => $propertyId,
                        ];
                    }
                }

                if ($row['attributes']['optionIds']) {
                    foreach ($row['attributes']['optionIds'] as $optionId) {
                        $options[] = [
                            'id' => $optionId,
                        ];
                    }
                }

                $categories = [];
                if (false === empty($row['attributes']['categoryIds'])) {
                    $categories = array_map(
                        static fn($categoryId): Shopware6ProductCategory => new Shopware6ProductCategory($categoryId),
                        $row['attributes']['categoryIds']
                    );
                }

                $media = [];
                if (false === empty($row['relationships']['media']['data'])) {
                    foreach ($row['relationships']['media']['data'] as $productMedia) {
                        if ($productMedia['type'] === 'product_media') {
                            $media[] = $includedMedia[$productMedia['id']];
                        }
                    }
                }

                $configuratorSettings = [];
                if (false === empty($row['relationships']['configuratorSettings']['data'])) {
                    foreach ($row['relationships']['configuratorSettings']['data'] as $configuratorSetting) {
                        if ($configuratorSetting['type'] === 'product_configurator_setting') {
                            $configuratorSettings[] = $includedConfiguratorSettings[$configuratorSetting['id']];
                        }
                    }
                }

                $translations = [];
                if (false === empty($row['relationships']['translations']['data'])) {
                    foreach ($row['relationships']['translations']['data'] as $translation) {
                        if ($translation['type'] === 'product_translation') {
                            $translations[] = $includedTranslations[$translation['id']];
                        }
                    }
                }

                $customFields = $row['attributes']['customFields'] ?: null;

                $result[] = new Shopware6Product(
                    $row['id'],
                    $row['attributes']['productNumber'],
                    $row['attributes']['name'],
                    $row['attributes']['description'] ?? null,
                    $properties,
                    $customFields,
                    $row['attributes']['parentId'] ?? null,
                    $options,
                    $row['attributes']['active'],
                    $row['attributes']['stock'] ?? null,
                    $row['attributes']['taxId'] ?? null,
                    $price,
                    $row['attributes']['coverId'] ?? null,
                    $row['attributes']['metaTitle'] ?? null,
                    $row['attributes']['metaDescription'] ?? null,
                    $row['attributes']['keywords'] ?? null,
                    $categories,
                    $media,
                    $configuratorSettings,
                    $translations
                );
            }
        }

        return $result;
    }

    private function getUri(): string
    {
        return rtrim(sprintf(self::URI, $this->query->getQuery()), '?');
    }
}
