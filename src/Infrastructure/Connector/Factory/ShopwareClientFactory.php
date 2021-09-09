<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Factory;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Connector\ClientFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ShopwareClientFactory implements ClientFactoryInterface
{
    public function create(Shopware6Channel $channel): ClientInterface
    {
        $config = [
            'base_uri' => $channel->getHost(),
        ];

        return new Client($config);
    }
}
