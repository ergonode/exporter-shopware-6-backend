<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\PropertyGroup;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\PropertyGroupMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6PropertyGroup;

class PropertyGroupNameMapper implements PropertyGroupMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public function map(
        Shopware6Channel $channel,
        Export $export,
        Shopware6PropertyGroup $shopware6PropertyGroup,
        AbstractAttribute $attribute,
        ?Language $language = null,
        ?Shopware6Language $shopware6Language = null
    ): Shopware6PropertyGroup {
        $name = $attribute->getLabel()->get($channel->getDefaultLanguage());
        if ($name) {
            $shopware6PropertyGroup->setName($name);
        }

        if ($language && $shopware6Language && $shopware6Language->getId()) {
            $translatedName = $attribute->getLabel()->get($language);
            if ($translatedName) {
                $shopware6PropertyGroup->addTranslations($shopware6Language->getId(), 'name', $translatedName);
            }
        }

        return $shopware6PropertyGroup;
    }
}
