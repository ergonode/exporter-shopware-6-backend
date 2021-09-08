<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Connector\ClientFactoryInterface;
use GuzzleHttp\ClientInterface;

class ShopwareTestClientFactory implements ClientFactoryInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function create(Shopware6Channel $channel): ClientInterface
    {
        return $this->client;
    }
}
