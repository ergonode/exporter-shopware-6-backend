<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as BaseKernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KernelBrowser extends BaseKernelBrowser
{
    public function get(string $url, bool $catchExceptions = false): Response
    {
        $this->request(
            Request::METHOD_GET,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            null,
            false,
            $catchExceptions,
        );

        return $this->response;
    }

    /**
     * @param mixed[] $parameters
     * @param mixed[] $files
     * @param mixed[] $server
     */
    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true,
        bool $catchExceptions = false
    ): Crawler {
        $this->catchExceptions($catchExceptions);

        return parent::request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory,
        );
    }
}
