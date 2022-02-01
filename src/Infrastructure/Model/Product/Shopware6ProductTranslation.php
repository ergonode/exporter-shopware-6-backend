<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model\Product;

class Shopware6ProductTranslation implements \JsonSerializable
{
    private string $id;
    private ?string $metaDescription;
    private ?string $name;
    private ?string $keywords;
    private ?string $description;
    private ?string $metaTitle;
    private ?string $packUnit;
    private ?string $packUnitPlural;
    private ?string $customSearchKeywords;
    private ?array $slotConfig;
    private ?array $customFields;
    private string $createdAt;
    private ?string $updatedAt;
    private string $productId;
    private string $languageId;
    private ?string $productVersionId;
    private ?string $apiAlias;

    public function __construct(
        string $id,
        ?string $metaDescription,
        ?string $name,
        ?string $keywords,
        ?string $description,
        ?string $metaTitle,
        ?string $packUnit,
        ?string $packUnitPlural,
        ?string $customSearchKeywords,
        ?array $slotConfig,
        ?array $customFields,
        string $createdAt,
        ?string $updatedAt,
        string $productId,
        string $languageId,
        ?string $productVersionId,
        ?string $apiAlias
    ) {
        $this->id = $id;
        $this->metaDescription = $metaDescription;
        $this->name = $name;
        $this->keywords = $keywords;
        $this->description = $description;
        $this->metaTitle = $metaTitle;
        $this->packUnit = $packUnit;
        $this->packUnitPlural = $packUnitPlural;
        $this->customSearchKeywords = $customSearchKeywords;
        $this->slotConfig = $slotConfig;
        $this->customFields = $customFields;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->productId = $productId;
        $this->languageId = $languageId;
        $this->productVersionId = $productVersionId;
        $this->apiAlias = $apiAlias;
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getCustomFields(): ?array
    {
        return $this->customFields;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function getPackUnit(): ?string
    {
        return $this->packUnit;
    }

    public function getPackUnitPlural(): ?string
    {
        return $this->packUnitPlural;
    }

    public function getCustomSearchKeywords(): ?string
    {
        return $this->customSearchKeywords;
    }

    public function getSlotConfig(): ?array
    {
        return $this->slotConfig;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function getProductVersionId(): ?string
    {
        return $this->productVersionId;
    }

    public function getApiAlias(): ?string
    {
        return $this->apiAlias;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'metaDescription' => $this->metaDescription,
            'metaTitle' => $this->metaTitle,
            'packUnit' => $this->packUnit,
            'packUnitPlural' => $this->packUnitPlural,
            'customSearchKeywords' => $this->customSearchKeywords,
            'slotConfig' => $this->slotConfig,
            'customFields' => $this->customFields,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'productId' => $this->productId,
            'languageId' => $this->languageId,
            'productVersionId' => $this->productVersionId,
            'apiAlias' => $this->apiAlias,
        ];
    }
}
