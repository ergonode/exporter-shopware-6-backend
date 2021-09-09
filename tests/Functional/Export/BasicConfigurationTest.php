<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional\Export;

use Ergonode\ExporterShopware6\Tests\Functional\ExporterShopware6TestCase;
use GuzzleHttp\Psr7\Response;

class BasicConfigurationTest extends ExporterShopware6TestCase
{
    public const BASIC_CHANNEL_ID = 'd8ba90e2-e4fe-4833-939c-682af913545a';

    public function testBasic(): void
    {
        $client = static::createClient();
        $client->login('test@ergonode.com');

        /** @var \GuzzleHttp\Handler\MockHandler $mock */
        $mock = static::$container->get('shopware_mock_handler');

        $mock->append(
            new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/OAuthToken.json')),
        ); //check api exist
        $mock->append(new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/OAuthToken.json')));
        $mock->append(new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/CurrencyGet.json')));
        $mock->append(new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/TaxGet.json')));
        $mock->append(new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/LanguageGet.json')));
        $mock->append(
            new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/Locale/Locale1_en-GB.json')),
        );
        $mock->append(
            new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/Locale/Locale2_de-DE.json')),
        );
        $mock->append(
            new Response(200, [], (string)file_get_contents(__DIR__ . '/../response/Locale/Locale3_pl-PL.json')),
        );

        $client->post('/api/v1/en_GB/channels/' . self::BASIC_CHANNEL_ID . '/exports', '{}');

        self::assertEquals(0, $mock->count());
        self::assertResponseStatusCodeSame(201);
    }
}
