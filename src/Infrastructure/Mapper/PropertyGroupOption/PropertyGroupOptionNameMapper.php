<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\PropertyGroupOption;

use Ergonode\Attribute\Domain\Entity\AbstractOption;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\PropertyGroupOptionMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6PropertyGroupOption;

class PropertyGroupOptionNameMapper implements PropertyGroupOptionMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public function map(
        Shopware6Channel $channel,
        Export $export,
        Shopware6PropertyGroupOption $propertyGroupOption,
        AbstractOption $option,
        ?Language $language = null,
        ?Shopware6Language $shopware6Language = null
    ): Shopware6PropertyGroupOption {
        $name = $option->getLabel()->get($channel->getDefaultLanguage());
        if ($name) {
            $propertyGroupOption->setName($name);
        }

        if ($language && $shopware6Language && $shopware6Language->getId()) {
            $translatedName = $option->getLabel()->get($language);
            if ($translatedName) {
                $propertyGroupOption->addTranslations($shopware6Language->getId(), 'name', $translatedName);
            }
        }

        return $propertyGroupOption;
    }
}
