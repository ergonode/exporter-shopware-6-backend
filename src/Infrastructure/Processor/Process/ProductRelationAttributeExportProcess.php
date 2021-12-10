<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\Channel\Domain\ValueObject\ExportLineId;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\LanguageRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Repository\ProductRelationAttributeRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Builder\ProductRelationAttributeBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Client\ProductRelationAttributeClient;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6ExporterException;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractProductCrossSelling;
use Ergonode\ExporterShopware6\Infrastructure\Model\Basic\ProductCrossSelling;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use GuzzleHttp\Exception\ClientException;
use Webmozart\Assert\Assert;

class ProductRelationAttributeExportProcess
{
    private ProductRelationAttributeBuilder $builder;

    private ProductRelationAttributeRepositoryInterface $productRelationAttributeRepository;

    private ProductRelationAttributeClient $relationAttributeClient;

    private LanguageRepositoryInterface $languageRepository;

    private ExportRepositoryInterface $exportRepository;

    public function __construct(
        ProductRelationAttributeBuilder $builder,
        ProductRelationAttributeRepositoryInterface $productRelationAttributeRepository,
        ProductRelationAttributeClient $relationAttributeClient,
        LanguageRepositoryInterface $languageRepository,
        ExportRepositoryInterface $exportRepository
    ) {
        $this->builder = $builder;
        $this->productRelationAttributeRepository = $productRelationAttributeRepository;
        $this->relationAttributeClient = $relationAttributeClient;
        $this->languageRepository = $languageRepository;
        $this->exportRepository = $exportRepository;
    }

    public function process(
        ExportLineId $lineId,
        Export $export,
        Shopware6Channel $channel,
        AbstractProduct $product,
        ProductRelationAttribute $attribute
    ): void {
        $productCrossSelling = $this->loadProductCrossSelling($channel, $product->getId(), $attribute->getId());

        try {
            if ($productCrossSelling) {
                $this->updateProductCrossSelling(
                    $channel,
                    $export,
                    $productCrossSelling,
                    $product,
                    $attribute,
                );
            } else {
                $this->createProductCrossSelling($channel, $export, $product, $attribute);
            }

            //update language
            $this->updateWithLanguages($channel, $export, $product, $attribute);
        } catch (Shopware6ExporterException $exception) {
            $this->exportRepository->addError($export->getId(), $exception->getMessage(), $exception->getParameters());
        }

        $this->exportRepository->processLine($lineId);
    }
    private function updateWithLanguages(
        Shopware6Channel $channel,
        Export $export,
        AbstractProduct $product,
        ProductRelationAttribute $attribute
    ): void {
        foreach ($channel->getLanguages() as $language) {
            if ($this->languageRepository->exists($channel->getId(), $language->getCode())) {
                $this->updateWithLanguage(
                    $channel,
                    $export,
                    $product,
                    $attribute,
                    $language,
                );
            }
        }
    }

    private function updateWithLanguage(
        Shopware6Channel $channel,
        Export $export,
        AbstractProduct $product,
        ProductRelationAttribute $attribute,
        Language $language
    ): void {
        $shopwareLanguage = $this->languageRepository->load($channel->getId(), $language->getCode());
        Assert::notNull($shopwareLanguage);

        $productCrossSelling = $this->loadProductCrossSelling(
            $channel,
            $product->getId(),
            $attribute->getId(),
            $shopwareLanguage,
        );
        Assert::notNull($productCrossSelling);

        $this->updateProductCrossSelling(
            $channel,
            $export,
            $productCrossSelling,
            $product,
            $attribute,
            $language,
            $shopwareLanguage,
        );
    }

    private function createProductCrossSelling(
        Shopware6Channel $channel,
        Export $export,
        AbstractProduct $product,
        ProductRelationAttribute $attribute
    ): void {
        $productCrossSelling = new ProductCrossSelling();

        $productCrossSelling = $this->builder->build(
            $channel,
            $export,
            $productCrossSelling,
            $product,
            $attribute,
        );

        $this->relationAttributeClient->insert(
            $channel,
            $productCrossSelling,
            $product->getId(),
            $attribute->getId(),
        );
    }

    private function updateProductCrossSelling(
        Shopware6Channel $channel,
        Export $export,
        AbstractProductCrossSelling $productCrossSelling,
        AbstractProduct $product,
        ProductRelationAttribute $attribute,
        ?Language $language = null,
        ?Shopware6Language $shopwareLanguage = null
    ): void {
        $productCrossSelling = $this->builder->build(
            $channel,
            $export,
            $productCrossSelling,
            $product,
            $attribute,
            $language,
        );
        if ($productCrossSelling->isModified()) {
            $this->relationAttributeClient->update(
                $channel,
                $productCrossSelling,
                $shopwareLanguage,
            );
        }
    }

    private function loadProductCrossSelling(
        Shopware6Channel $channel,
        ProductId $productId,
        AttributeId $attributeId,
        ?Shopware6Language $shopware6Language = null
    ): ?AbstractProductCrossSelling {
        $shopwareId = $this->productRelationAttributeRepository->load($channel->getId(), $productId, $attributeId);
        if ($shopwareId) {
            try {
                return $this->relationAttributeClient->get($channel, $shopwareId, $shopware6Language);
            } catch (ClientException $exception) {
            }
        }

        return null;
    }
}
