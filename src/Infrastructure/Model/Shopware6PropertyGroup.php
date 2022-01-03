<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model;

class Shopware6PropertyGroup implements \JsonSerializable
{
    private const DISPLAY_TYPES = ['media', 'text', 'color'];
    private const SORTING_TYPES = ['numeric', 'alphanumeric', 'position'];

    protected ?string $id;

    protected ?string $name;

    protected ?string $displayType;

    protected ?string $sortingType;

    protected bool $modified = false;

    private ?array $translations;

    public function __construct(
        ?string $id = null,
        ?string $name = null,
        ?string $displayType = 'text',
        ?string $sortingType = 'alphanumeric',
        ?array $translations = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->displayType = $displayType;
        $this->sortingType = $sortingType;
        $this->translations = $translations;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($name !== $this->name) {
            $this->name = $name;
            $this->modified = true;
        }
    }

    public function addTranslations(string $shopwareLanguageId, string $field, string $value): void
    {
        if (!(isset($this->translations[$shopwareLanguageId][$field]) && $this->translations[$shopwareLanguageId][$field] === $value)) {
            $this->modified = true;
            $this->translations[$shopwareLanguageId][$field] = $value;
        }
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function setDisplayType(string $displayType): void
    {
        if ($displayType !== $this->displayType && in_array($displayType, self::DISPLAY_TYPES)) {
            $this->displayType = $displayType;
            $this->modified = true;
        }
    }

    public function getSortingType(): string
    {
        return $this->sortingType;
    }

    public function setSortingType(string $sortingType): void
    {
        if ($sortingType !== $this->sortingType && in_array($sortingType, self::SORTING_TYPES)) {
            $this->sortingType = $sortingType;
            $this->modified = true;
        }
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'name'        => $this->name,
            'displayType' => $this->displayType,
            'sortingType' => $this->sortingType,
        ];
        if ($this->translations) {
            $data['translations'] = $this->translations;
        }

        return $data;
    }
}
