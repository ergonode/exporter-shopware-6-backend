<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model;

use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductCategory;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductConfiguratorSettings;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductMedia;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductPrice;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductTranslation;
use JsonSerializable;
use Webmozart\Assert\Assert;

class Shopware6Product implements JsonSerializable
{

    private ?string $id;

    private ?string $sku;

    private ?string $name;

    private ?string $description;

    /**
     * @var Shopware6ProductCategory[]
     */
    private array $categories = [];

    /**
     * @var array|null
     */
    private ?array $properties;

    /**
     * @var array|null
     */
    private ?array $customFields;

    private bool $active;

    private ?int $stock;

    private ?string $taxId;

    /**
     * @var Shopware6ProductPrice[]|null
     */
    private ?array $price;

    private ?string $parentId;

    /**
     * @var array|null
     */
    private ?array $options;

    /**
     * @var array
     */
    private array $optionsToRemove = [];

    /**
     * @var Shopware6ProductMedia[]
     */
    private array $media = [];

    /**
     * @var Shopware6ProductConfiguratorSettings[]
     */
    private array $configuratorSettings = [];

    private ?string $coverId;

    private ?string $metaTitle;

    private ?string $metaDescription;

    private ?string $keywords;

    /**
     * @var array
     */
    private array $propertyToRemove = [];

    /**
     * @var array
     */
    private array $categoryToRemove = [];

    /**
     * @var array
     */
    private array $mediaToRemove = [];

    private bool $modified = false;

    /**
     * @var Shopware6ProductTranslation[]
     */
    private array $translations;

    /**
     * @param string|null $id
     * @param string|null $sku
     * @param string|null $name
     * @param string|null $description
     * @param array|null $properties
     * @param array|null $customFields
     * @param string|null $parentId
     * @param array|null $options
     * @param bool $active
     * @param int|null $stock
     * @param string|null $taxId
     * @param array|null $price
     * @param string|null $coverId
     * @param string|null $metaTitle
     * @param string|null $metaDescription
     * @param string|null $keywords
     * @param Shopware6ProductCategory[] $categories
     * @param Shopware6ProductMedia[] $media
     * @param Shopware6ProductConfiguratorSettings[] $configuratorSettings
     * @param Shopware6ProductTranslation[] $translations
     */
    public function __construct(
        ?string $id = null,
        ?string $sku = null,
        ?string $name = null,
        ?string $description = null,
        ?array $properties = null,
        ?array $customFields = null,
        ?string $parentId = null,
        ?array $options = null,
        bool $active = true,
        ?int $stock = null,
        ?string $taxId = null,
        ?array $price = null,
        ?string $coverId = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $keywords = null,
        array $categories = [],
        array $media = [],
        array $configuratorSettings = [],
        array $translations = []
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->description = $description;
        $this->properties = $properties;
        $this->customFields = $customFields;
        $this->parentId = $parentId;
        $this->options = $options;
        $this->active = $active;
        $this->stock = $stock;
        $this->taxId = $taxId;
        $this->price = $price;
        $this->coverId = $coverId;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->keywords = $keywords;
        $this->translations = $translations;
        $this->setPropertyToRemove($properties);
        $this->setCategories($categories);
        $this->setMedia($media);
        $this->setConfiguratorSettings($configuratorSettings);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        if ($sku !== $this->sku) {
            $this->sku = $sku;
            $this->setModified();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        if ($name !== $this->name) {
            $this->name = $name;
            $this->setModified();
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        if ($description !== $this->description) {
            $this->description = $description;
            $this->setModified();
        }
    }

    /**
     * @param Shopware6ProductCategory[] $categories
     */
    public function setCategories(array $categories): void
    {
        Assert::allIsInstanceOf($categories, Shopware6ProductCategory::class);
        $this->categories = $categories;
        $this->setCategoryToRemove($categories);
    }

    /**
     * @return Shopware6ProductCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function addCategory(Shopware6ProductCategory $category): void
    {
        if (!$this->hasCategory($category)) {
            $this->categories[] = $category;
            $this->setModified();
        }
        unset($this->categoryToRemove[$category->getId()]);
    }

    public function hasCategory(Shopware6ProductCategory $category): bool
    {
        foreach ($this->getCategories() as $productCategory) {
            if ($productCategory->getId() === $category->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCategoryToRemove(): array
    {
        return $this->categoryToRemove;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        if ($this->properties) {
            return $this->properties;
        }

        return [];
    }

    public function addProperty(string $propertyId): void
    {
        if (!$this->hasProperty($propertyId)) {
            $this->properties[] = [
                'id' => $propertyId,
            ];
            $this->setModified();
        }
        unset($this->propertyToRemove[$propertyId]);
    }

    public function hasProperty(string $propertyId): bool
    {
        foreach ($this->getProperties() as $property) {
            if ($property['id'] === $propertyId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPropertyToRemove(): array
    {
        return $this->propertyToRemove;
    }

    /**
     * @return array|null
     */
    public function getCustomFields(): ?array
    {
        if ($this->customFields) {
            return $this->customFields;
        }

        return [];
    }

    /**
     * @param string|array $value
     */
    public function addCustomField(string $customFieldId, $value): void
    {
        if ($this->hasCustomField($customFieldId)) {
            if ($this->customFields[$customFieldId] !== $value) {
                $this->customFields[$customFieldId] = $value;
                $this->setModified();
            }
        } else {
            $this->customFields[$customFieldId] = $value;
            $this->setModified();
        }
    }

    public function hasCustomField(string $customFieldId): bool
    {
        foreach (array_keys($this->getCustomFields()) as $key) {
            if ($key === $customFieldId) {
                return true;
            }
        }

        return false;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        if ($active !== $this->active) {
            $this->active = $active;
            $this->setModified();
        }
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        if ($stock !== $this->stock) {
            $this->stock = $stock;
            $this->setModified();
        }
    }

    public function getTaxId(): string
    {
        return $this->taxId;
    }

    public function setTaxId(string $taxId): void
    {
        if ($taxId !== $this->taxId) {
            $this->taxId = $taxId;
            $this->setModified();
        }
    }

    /**
     * @return array
     */
    public function getPrice(): array
    {
        if ($this->price) {
            return $this->price;
        }

        return [];
    }

    public function addPrice(Shopware6ProductPrice $price): void
    {
        if (!$this->hasPrice($price)) {
            $this->price[] = $price;
            $this->setModified();
        }
        $this->changePrice($price);
    }

    public function hasPrice(Shopware6ProductPrice $price): bool
    {
        foreach ($this->getPrice() as $item) {
            if ($item->getCurrencyId() === $price->getCurrencyId()) {
                return true;
            }
        }

        return false;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        if ($parentId !== $this->parentId) {
            $this->parentId = $parentId;
            $this->setModified();
        }
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        if ($this->options) {
            return $this->options;
        }

        return [];
    }

    public function addOptions(string $option): void
    {
        if (!$this->hasOption($option)) {
            $this->options[] = [
                'id' => $option,
            ];
            $this->setModified();
        }
    }

    public function hasOption(string $optionId): bool
    {
        foreach ($this->getOptions() as $option) {
            if ($option['id'] === $optionId) {
                return true;
            }
        }

        return false;
    }

    public function addOptionToRemove(string $option): void
    {
        if ($this->hasOption($option)) {
            $this->optionsToRemove[] = $option;
        }
    }

    /**
     * @return string[]
     */
    public function getOptionsToRemove(): array
    {
        return $this->optionsToRemove;
    }

    /**
     * @param Shopware6ProductMedia[] $media
     */
    public function setMedia(array $media): void
    {
        Assert::allIsInstanceOf($media, Shopware6ProductMedia::class);
        $this->media = $media;
        $this->setMediaToRemove($media);
    }

    /**
     * @return Shopware6ProductMedia[]
     */
    public function getMedia(): array
    {
        return $this->media;
    }

    public function addMedia(Shopware6ProductMedia $media): void
    {
        if (!$this->hasMedia($media)) {
            $this->media[] = $media;
            $this->setModified();
        }
        unset($this->mediaToRemove[$media->getMediaId()]);
    }

    public function hasMedia(Shopware6ProductMedia $media): bool
    {
        foreach ($this->getMedia() as $productMedia) {
            if ($media->getMediaId() === $productMedia->getMediaId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Shopware6ProductMedia[]
     */
    public function getMediaToRemove(): array
    {
        return $this->mediaToRemove;
    }

    /**
     * @param Shopware6ProductConfiguratorSettings[] $configuratorSettings
     */
    public function setConfiguratorSettings(array $configuratorSettings): void
    {
        Assert::allIsInstanceOf($configuratorSettings, Shopware6ProductConfiguratorSettings::class);
        $this->configuratorSettings = $configuratorSettings;
    }

    /**
     * @return Shopware6ProductConfiguratorSettings[]
     */
    public function getConfiguratorSettings(): array
    {
        return $this->configuratorSettings;
    }

    public function addConfiguratorSettings(Shopware6ProductConfiguratorSettings $configuratorSetting): void
    {
        if (!$this->hasConfiguratorSettings($configuratorSetting)) {
            $this->configuratorSettings[] = $configuratorSetting;
            $this->setModified();
        }
    }

    public function hasConfiguratorSettings(Shopware6ProductConfiguratorSettings $value): bool
    {
        foreach ($this->getConfiguratorSettings() as $configuratorSetting) {
            if ($configuratorSetting->getOptionId() === $value->getOptionId()) {
                return true;
            }
        }

        return false;
    }

    public function getCoverId(): ?string
    {
        return $this->coverId;
    }

    public function setCoverId(?string $coverId): void
    {
        if ($this->coverId !== $coverId) {
            $this->coverId = $coverId;
            $this->setModified();
        }
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        if ($this->metaTitle !== $metaTitle) {
            $this->metaTitle = $metaTitle;
            $this->setModified();
        }
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        if ($this->metaDescription !== $metaDescription) {
            $this->metaDescription = $metaDescription;
            $this->setModified();
        }
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        if ($this->keywords !== $keywords) {
            $this->keywords = $keywords;
            $this->setModified();
        }
    }


    public function isNew(): bool
    {
        return null === $this->id;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function hasItemToRemoved(): bool
    {
        return count($this->propertyToRemove) > 0
            || count($this->categoryToRemove) > 0
            || count($this->mediaToRemove) > 0
            || count($this->optionsToRemove) > 0;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'productNumber' => $this->sku,
            'name' => $this->name,
        ];

        if (null !== $this->description) {
            $data['description'] = $this->description;
        }
        foreach ($this->categories as $category) {
            $data['categories'][] = $category->jsonSerialize();
        }
        if (null !== $this->properties) {
            $data['properties'] = $this->properties;
        }
        if ($this->customFields) {
            $data['customFields'] = $this->customFields;
        }

        $data['active'] = $this->active;

        if (null !== $this->stock) {
            $data['stock'] = $this->stock;
        }
        if (null !== $this->taxId) {
            $data['taxId'] = $this->taxId;
        }
        if (null !== $this->price) {
            foreach ($this->price as $price) {
                $data['price'][] = $price->jsonSerialize();
            }
        }
        if (null !== $this->parentId) {
            $data['parentId'] = $this->parentId;
        }
        if (null !== $this->options) {
            $data['options'] = $this->options;
        }
        foreach ($this->media as $media) {
            $data['media'][] = $media->jsonSerialize();
        }
        foreach ($this->configuratorSettings as $configuratorSetting) {
            $data['configuratorSettings'][] = $configuratorSetting->jsonSerialize();
        }
        if (null !== $this->coverId) {
            $data['coverId'] = $this->coverId;
        }
        if (null !== $this->metaTitle) {
            $data['metaTitle'] = $this->metaTitle;
        }
        if (null !== $this->metaDescription) {
            $data['metaDescription'] = $this->metaDescription;
        }
        if (null !== $this->keywords) {
            $data['keywords'] = $this->keywords;
        }

        return $data;
    }

    /**
     * @param array|null $property
     */
    private function setPropertyToRemove(?array $property): void
    {
        if ($property) {
            foreach ($property as $item) {
                $this->propertyToRemove[$item['id']] = $item['id'];
            }
        }
    }

    /**
     * @param Shopware6ProductCategory[] $categories
     */
    private function setCategoryToRemove(array $categories): void
    {
        foreach ($categories as $item) {
            $id = $item->getId();
            $this->categoryToRemove[$id] = $id;
        }
    }

    /**
     * @param array $media
     */
    private function setMediaToRemove(array $media): void
    {
        foreach ($media as $item) {
            $this->mediaToRemove[$item->getMediaId()] = $item;
        }
    }

    private function changePrice(Shopware6ProductPrice $price): void
    {
        foreach ($this->getPrice() as $item) {
            if (!$item->isEqual($price) && $item->getCurrencyId() === $price->getCurrencyId()) {
                $item->setNet($price->getNet());
                $item->setGross($price->getGross());
                $this->setModified();
            }
        }
    }

    private function setModified(): void
    {
        $this->modified = false;
    }

    public function getTranslated(Shopware6Language $language): Shopware6Product
    {
        $translation = null;
        $languageId = $language->getId();
        foreach ($this->translations as $translationEntry) {
            if ($translationEntry->getLanguageId() === $languageId) {
                $translation = $translationEntry;
                break;
            }
        }

        $name = null;
        $description = null;
        $customFields = null;
        $metaTitle = null;
        $metaDescription = null;
        $keywords = null;

        if (null !== $translation) {
            $name = $translation->getName();
            $description = $translation->getDescription();
            $customFields = $translation->getCustomFields();
            $metaTitle = $translation->getMetaTitle();
            $metaDescription = $translation->getMetaDescription();
            $keywords = $translation->getKeywords();
        }

        return new Shopware6Product(
            $this->id,
            $this->sku,
            $name,
            $description,
            $this->properties,
            $customFields,
            $this->parentId,
            $this->options,
            $this->active,
            $this->stock,
            $this->taxId,
            $this->price,
            $this->coverId,
            $metaTitle,
            $metaDescription,
            $keywords,
            $this->categories,
            $this->media,
            $this->configuratorSettings,
            $this->translations
        );
    }
}
