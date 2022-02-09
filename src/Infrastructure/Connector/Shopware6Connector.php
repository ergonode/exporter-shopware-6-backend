<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\PostAccessToken;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6AuthenticationException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class Shopware6Connector
{
    private Configurator $configurator;

    private LoggerInterface $logger;

    private ?string $token;

    private DateTimeInterface $expiresAt;

    public function __construct(Configurator $configurator, LoggerInterface $logger)
    {
        $this->configurator = $configurator;
        $this->logger = $logger;

        $this->token = null;
        $this->expiresAt = new DateTimeImmutable();
    }

    /**
     * @return array|object|string|null
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(Shopware6Channel $channel, AbstractAction $action)
    {
        if ($this->token === null || $this->expiresAt <= (new DateTime())) {
            $this->requestToken($channel);
        }

        return $this->request($channel, $action);
    }

    /**
     * @param Shopware6Channel $channel
     * @param AbstractAction $action
     * @return array|object|string|null
     *
     * @throws GuzzleException
     */
    private function request(Shopware6Channel $channel, AbstractAction $action)
    {
        $actionUid = uniqid('sh6_', true);
        try {
            $config = [
                'base_uri' => $channel->getHost(),
            ];

            $this->configurator->configure($action, $this->token);
            if ($action->isLoggable()) {
                $this->logRequest($actionUid, $action);
            }

            $client = new Client($config);

            $response = $client->send($action->getRequest());
            $contents = $this->resolveResponse($response);
            if ($action->isLoggable()) {
                $this->logResponse($actionUid, $response, $contents);
            }

            return $action->parseContent($contents);
        } catch (ClientException $exception) {
            $this->logClientException($exception, $actionUid);
            throw $exception;
        } catch (GuzzleException|Exception $exception) {
            $this->logger->error($exception, ['action_id' => $actionUid]);
            throw  $exception;
        }
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    private function requestToken(Shopware6Channel $channel): void
    {
        try {
            $post = new PostAccessToken($channel);
            $data = $this->request($channel, $post);
            $this->token = $data['access_token'];
            $this->expiresAt = $this->calculateExpiryTime((int)$data['expires_in']);
        } catch (ClientException $exception) {
            if ($exception->getCode() === 401) {
                throw new Shopware6AuthenticationException($exception);
            }
            throw $exception;
        }
    }

    private function calculateExpiryTime(int $expiresIn): DateTimeInterface
    {
        $expiryTimestamp = (new DateTime())->getTimestamp() + $expiresIn;

        return (new DateTimeImmutable())->setTimestamp($expiryTimestamp);
    }

    private function resolveResponse(ResponseInterface $response): ?string
    {
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();

        switch ($statusCode) {
            case Response::HTTP_OK:
            case Response::HTTP_CREATED:
            case Response::HTTP_ACCEPTED:
                return $contents;
            case Response::HTTP_NO_CONTENT:
                return null;
        }
        throw new RuntimeException(sprintf('Unsupported response status "%s" ', $statusCode));
    }

    private function logRequest(string $uid, ActionInterface $action): void
    {
        $requestMethod = $action->getRequest()->getMethod();
        $requestPath = $action->getRequest()->getUri()->getPath();
        $body = $action->getRequest()->getBody();
        $bodyContents = null;
        if ($body !== null) {
            $bodyContents = $body->getContents();
        }

        $this->logger->debug(
            'Shopware6 REQUEST',
            [
                'action_id' => $uid,
                'path' => $requestPath,
                'method' => $requestMethod,
                'headers' => $action->getRequest()->getHeaders(),
                'body' => $bodyContents,
                'query' => $action->getRequest()->getUri()->getQuery(),
            ]
        );
    }

    private function logResponse(string $uid, ResponseInterface $response, ?string $contents): void
    {
        $this->logger->debug(
            'Shopware6 RESPONSE',
            [
                'action_id' => $uid,
                'status' => $response->getStatusCode(),
                'body' => $contents,
            ]
        );
    }

    private function logClientException(ClientException $exception, string $actionUid): void
    {
        $response = $exception->getResponse();
        $bodyContents = null;
        if ($response !== null) {
            $bodyContents = $response->getBody()->getContents();
        }
        $this->logger->error(
            $exception,
            [
                'action_id' => $actionUid,
                'exception_message' => $exception->getMessage(),
                'body' => json_decode($bodyContents, true),
            ]
        );
    }
}
