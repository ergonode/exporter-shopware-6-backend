<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional;

class ApiTest extends ExporterShopware6TestCase
{
    public function testGetProductTypeDictionary(): void
    {
        $client = static::createClient();

        $client->get('/api/v1/pl_PL/dictionary/product-type');

        self::assertResponseStatusCodeSame(200);
    }
}
