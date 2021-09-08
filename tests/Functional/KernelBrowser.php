<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
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
    /**
     * @var string[]
     */
    private array $headers = [];

    public function get(string $url, bool $catchExceptions = false): Response
    {
        $this->request(
            Request::METHOD_GET,
            $url,
            [],
            [],
            array_merge($this->headers, ['CONTENT_TYPE' => 'application/json']),
            null,
            false,
            $catchExceptions,
        );

        return $this->response;
    }

    public function post(string $uri, ?string $content = null, bool $catchExceptions = false): Response
    {
        $this->request(
            'POST',
            $uri,
            [],
            [],
            array_merge($this->headers, ['CONTENT_TYPE' => 'application/json']),
            $content,
            true,
            $catchExceptions,
        );

        return $this->response;
    }

    public function login(string $email): void
    {
        $response = $this->post(
            '/api/v1/login',
            json_encode(
                [
                    'username' => $email,
                    'password' => 'abcd1234',
                ],
                JSON_THROW_ON_ERROR,
            ),
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception("Failed to login. {$response->getContent()}");
        }

        $content = $this->getJsonResponseContent();

        $this->headers['HTTP_AUTHORIZATION'] = "Bearer {$content['token']}";
    }

    /**
     * @throws \JsonException
     *
     * @return mixed
     */
    public function getJsonResponseContent()
    {
        return json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);
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
        ?string $content = null,
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
