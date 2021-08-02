<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Attribute\Domain\Repository\OptionRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\PropertyGroupOptionsRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Repository\PropertyGroupRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductConfiguratorSettings;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Ergonode\Product\Domain\Repository\ProductRepositoryInterface;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Webmozart\Assert\Assert;

class ProductVariantMapper extends AbstractVariantOptionMapper
{
    private PropertyGroupRepositoryInterface $propertyGroupRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        PropertyGroupRepositoryInterface $propertyGroupRepository,
        AttributeRepositoryInterface $attributeRepository,
        OptionRepositoryInterface $optionRepository,
        TranslationInheritanceCalculator $calculator,
        PropertyGroupOptionsRepositoryInterface $propertyGroupOptionsRepository,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($attributeRepository, $optionRepository, $calculator, $propertyGroupOptionsRepository);

        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->productRepository = $productRepository;
    }

    public function map(
        Shopware6Channel $channel,
        Export $export,
        Shopware6Product $shopware6Product,
        AbstractProduct $product,
        ?Language $language = null
    ): Shopware6Product {
        if ($product instanceof VariableProduct) {
            $this->variantMapper($channel, $shopware6Product, $product);
        }

        return $shopware6Product;
    }

    private function variantMapper(
        Shopware6Channel $channel,
        Shopware6Product $shopware6Product,
        VariableProduct $product
    ): Shopware6Product {
        foreach ($product->getBindings() as $bindingId) {
            if ($this->propertyGroupRepository->exists($channel->getId(), $bindingId)) {
                $this->mapOptions($channel, $shopware6Product, $bindingId, $product->getChildren());
            }
        }

        return $shopware6Product;
    }


    private function mapOptions(
        Shopware6Channel $channel,
        Shopware6Product $shopware6Product,
        AttributeId $bindingId,
        array $childrenId
    ): Shopware6Product {
        foreach ($childrenId as $childId) {
            $child = $this->productRepository->load($childId);
            Assert::isInstanceOf($child, AbstractProduct::class);

            $shopwareId = $this->optionMapper($bindingId, $child, $channel);
            if ($shopwareId) {
                $shopware6Product->addConfiguratorSettings(
                    new Shopware6ProductConfiguratorSettings(null, $shopwareId)
                );
            }
        }

        return $shopware6Product;
    }
}
