<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Client;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Language\GetLanguageList;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6Connector;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6QueryBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;

class Shopware6LanguageClient
{
    private Shopware6Connector $connector;

    public function __construct(Shopware6Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param Shopware6Channel $channel
     * @return Shopware6Language[]
     * @throws \Exception
     */
    public function getLanguageList(Shopware6Channel $channel): array
    {
        $query = $this->getQueryBuilder();
        $action = new GetLanguageList($query);

        return $this->connector->execute($channel, $action);
    }

    private function getQueryBuilder(): Shopware6QueryBuilder
    {
        $query = new Shopware6QueryBuilder();
        $query->limit(500);
        // associations[locale][]=
        $query->association('locale', ['' => '']);
        return $query;
    }
}
