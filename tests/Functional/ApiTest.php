<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional;

class ApiTest extends ExporterShopware6TestCase
{
    public function testGetChannelConfiguration(): void
    {
        $client = static::createClient();

        $client->login('test@ergonode.com');

        $client->get('/api/v1/en_GB/channels/shopware-6-api/configuration');

        self::assertResponseStatusCodeSame(200);
    }
}
