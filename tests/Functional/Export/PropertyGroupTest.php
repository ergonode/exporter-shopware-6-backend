<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional\Export;

use Ergonode\ExporterShopware6\Tests\Functional\ExporterShopware6TestCase;
use GuzzleHttp\Psr7\Response;

class PropertyGroupTest extends ExporterShopware6TestCase
{
    public const PROPERTY_CHANNEL_ID = '701b4281-87bd-4384-bb8c-c7e259da0f0f';

    public function testProperty(): void
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
        $mock->append(
            new Response(
                200,
                [],
                (string)file_get_contents(__DIR__ . '/../response/PropertyGroup/Property1_SelectLocal.json'),
            ),
        );
        $mock->append(
            new Response(
                200,
                [],
                (string)file_get_contents(__DIR__ . '/../response/PropertyGroup/Property1_SelectLocalOption1.json'),
            ),
        );
        $mock->append(
            new Response(
                200,
                [],
                (string)file_get_contents(__DIR__ . '/../response/PropertyGroup/Property1_SelectLocalOption2.json'),
            ),
        );
        $mock->append(
            new Response(
                200,
                [],
                (string)file_get_contents(__DIR__ . '/../response/PropertyGroup/Property1_SelectLocalOption3.json'),
            ),
        );
        $mock->append(
            new Response(
                200,
                [],
                (string)file_get_contents(__DIR__ . '/../response/PropertyGroup/Property1_SelectLocalOption4.json'),
            ),
        );


        $client->post('/api/v1/en_GB/channels/' . self::PROPERTY_CHANNEL_ID . '/exports', '{}');

        self::assertEquals(0, $mock->count());
        self::assertResponseStatusCodeSame(201);
    }
}
