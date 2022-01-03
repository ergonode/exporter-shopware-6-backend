<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action\CustomField;

use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use Ergonode\ExporterShopware6\Infrastructure\Model\PropertyGroupOption\BatchPropertyGroupOption;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BatchPostPropertyGroupOptionAction extends AbstractAction
{
    private const URI = '/api/_action/sync?%s';

    protected bool $response;

    protected BatchPropertyGroupOption $batchPropertyGroupOption;

    public function __construct(BatchPropertyGroupOption $batchPropertyGroupOption, bool $response = false)
    {
        $this->response = $response;
        $this->batchPropertyGroupOption = $batchPropertyGroupOption;
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
            $result[$requestName] = $row['result'][0]['entities']['property_group_option'][0];
        }

        return $result;
    }

    private function buildBody(): string
    {
        return json_encode($this->batchPropertyGroupOption->jsonSerialize(), JSON_THROW_ON_ERROR);
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
