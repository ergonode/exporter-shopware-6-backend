<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Attribute\Domain\Repository\OptionRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\PropertyGroupOptionsRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductMapperInterface;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\AggregateId;
use Webmozart\Assert\Assert;

abstract class AbstractVariantOptionMapper implements ProductMapperInterface
{
    protected AttributeRepositoryInterface $attributeRepository;

    protected OptionRepositoryInterface $optionRepository;

    protected TranslationInheritanceCalculator $calculator;

    protected PropertyGroupOptionsRepositoryInterface $propertyGroupOptionsRepository;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        OptionRepositoryInterface $optionRepository,
        TranslationInheritanceCalculator $calculator,
        PropertyGroupOptionsRepositoryInterface $propertyGroupOptionsRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->optionRepository = $optionRepository;
        $this->calculator = $calculator;
        $this->propertyGroupOptionsRepository = $propertyGroupOptionsRepository;
    }

    protected function optionMapper(
        AttributeId $bindingId,
        AbstractProduct $product,
        Shopware6Channel $channel
    ): ?string {
        $binding = $this->attributeRepository->load($bindingId);
        Assert::notNull($binding);
        if (false === $product->hasAttribute($binding->getCode())) {
            return null;
        }
        $value = $product->getAttribute($binding->getCode());
        $optionValue = $this->calculator->calculate($binding->getScope(), $value, $channel->getDefaultLanguage());
        $optionId = new AggregateId($optionValue);

        return $this->optionMap($channel, $bindingId, $optionId);
    }

    protected function optionMap(Shopware6Channel $channel, AttributeId $bindingId, AggregateId $optionId): ?string
    {
        return $this->propertyGroupOptionsRepository->load(
            $channel->getId(),
            $bindingId,
            $optionId,
        );
    }
}
