<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\PropertyGroupOptionsRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6PropertyGroupOptionClient;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\AggregateId;

abstract class AbstractShopware6ProductPropertyGroupOptionMapper extends AbstractProductPropertyGroupMapper
{
    private PropertyGroupOptionsRepositoryInterface $propertyGroupOptionsRepository;

    public function __construct(
        AttributeRepositoryInterface $repository,
        Shopware6PropertyGroupOptionClient $propertyGroupOptionClient,
        TranslationInheritanceCalculator $calculator,
        PropertyGroupOptionsRepositoryInterface $propertyGroupOptionsRepository
    ) {
        parent::__construct($repository, $propertyGroupOptionClient, $calculator);
        $this->propertyGroupOptionsRepository = $propertyGroupOptionsRepository;
    }

    protected function addProperty(
        Shopware6Product $shopware6Product,
        AbstractAttribute $attribute,
        AbstractProduct $product,
        Shopware6Channel $channel,
        ?Language $language = null
    ): Shopware6Product {

        $value = $product->getAttribute($attribute->getCode());
        $calculateValue = $this->calculator->calculate($attribute->getScope(), $value, $channel->getDefaultLanguage());
        if (is_array($calculateValue)) {
            foreach ($calculateValue as $optionValue) {
                $optionId = new AggregateId($optionValue);

                $propertyId = $this->propertyGroupOptionsRepository->load(
                    $channel->getId(),
                    $attribute->getId(),
                    $optionId,
                );
                if ($propertyId) {
                    $shopware6Product->addProperty($propertyId);
                }
            }
        } elseif (AggregateId::isValid($calculateValue)) {
            $optionId = new AggregateId($calculateValue);

            $propertyId = $this->propertyGroupOptionsRepository->load(
                $channel->getId(),
                $attribute->getId(),
                $optionId,
            );
            if ($propertyId) {
                $shopware6Product->addProperty($propertyId);
            }
        }

        return $shopware6Product;
    }
}
