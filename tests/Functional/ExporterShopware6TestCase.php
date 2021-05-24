<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional;

use Doctrine\DBAL\Connection;
use Ergonode\ExporterShopware6\Tests\Functional\Fixtures\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @method KernelBrowser createClient()
 */
class ExporterShopware6TestCase extends WebTestCase
{
    protected static Connection $connection;

    /**
     * @param mixed[] $options
     */
    protected static function bootKernel(array $options = []): KernelInterface
    {
        $kernel = parent::bootKernel($options);
        /** @var Connection $connection */
        $connection = static::$container->get(Connection::class);
        static::$connection = $connection;

        return $kernel;
    }

    /**
     * @param mixed[] $options
     */
    protected static function createKernel(array $options = []): Kernel
    {
        return new Kernel('test', false);
    }
}
