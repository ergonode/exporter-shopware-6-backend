<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector\Action;

use Ergonode\ExporterShopware6\Infrastructure\Model\BatchVisibilitiesProduct;
use Ergonode\ExporterShopware6\Infrastructure\Connector\AbstractAction;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BatchDeleteProductVisiblity extends AbstractAction
{
    private const URI = '/api/_action/sync';

    protected BatchVisibilitiesProduct $batchVisibilitiesProduct;

    public function __construct(BatchVisibilitiesProduct $batchVisibilitiesProduct)
    {
        $this->batchVisibilitiesProduct = $batchVisibilitiesProduct;
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
     * @return null
     */
    public function parseContent(?string $content)
    {
        return null;
    }

    private function buildBody(): string
    {
        return json_encode($this->batchVisibilitiesProduct->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    private function getUri(): string
    {
        return self::URI;
    }
}
