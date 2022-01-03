<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\CustomField;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Model\CustomField\BatchCustomField;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BatchPostCustomFieldAction extends AbstractAction
{
    private const URI = '/api/_action/sync?%s';

    protected bool $response;

    protected BatchCustomField $batchCustomField;

    public function __construct(BatchCustomField $batchCustomField, bool $response = false)
    {
        $this->response = $response;
        $this->batchCustomField = $batchCustomField;
    }

    public function getRequest(): Request
    {
        return new Request(
            HttpRequest::METHOD_POST,
            $this->getUri(),
            $this->buildHeaders(),
            $this->buildBody()
        );
    }

    /**
     * @param string|null $content
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function parseContent(?string $content): array
    {
        $result = [];
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['data'] as $requestName => $row) {
            $result[$requestName] = $row['result'][0]['entities']['custom_field'][0];
        }

        return $result;
    }

    private function buildBody(): string
    {
        return json_encode($this->batchCustomField->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    private function getUri(): string
    {
        $query = [];
        if ($this->response) {
            $query['_response'] = 'true';
        }

        return rtrim(sprintf(self::URI, http_build_query($query)), '?');
    }
}
