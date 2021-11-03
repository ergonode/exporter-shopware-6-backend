<?php

/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Client;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\BatchDeleteProductVisiblity;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\BatchPostProductVisiblity;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\GetProductProductVisibility;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6Connector;
use Ergonode\ExporterShopware6\Infrastructure\Model\BatchVisibilitiesProduct;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Language;
use Ergonode\ExporterShopware6\Infrastructure\Model\VisibilitiesProduct;
use Exception;

class ProductVisibilityClient
{
    private Shopware6Connector $connector;

    public function __construct(Shopware6Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param Shopware6Channel $channel
     * @param string $shopwareId
     * @param Shopware6Language|null $shopware6Language
     * @return VisibilitiesProduct[]|null
     * @throws Exception
     */
    public function get(
        Shopware6Channel $channel,
        string $shopwareId,
        ?Shopware6Language $shopware6Language = null
    ): ?array {
        $action = new GetProductProductVisibility($shopwareId);
        if ($shopware6Language) {
            $action->addHeader('sw-language-id', $shopware6Language->getId());
        }

        return $this->connector->execute($channel, $action);
    }

    /**
     * @param Shopware6Channel $channel
     * @param BatchVisibilitiesProduct $visibilitiesProduct
     * @param Shopware6Language|null $shopware6Language
     * @return void
     * @throws Exception
     */
    public function update(
        Shopware6Channel $channel,
        BatchVisibilitiesProduct $visibilitiesProduct,
        ?Shopware6Language $shopware6Language = null
    ): void {
        $action = new BatchPostProductVisiblity($visibilitiesProduct);
        if ($shopware6Language) {
            $action->addHeader('sw-language-id', $shopware6Language->getId());
        }
        $this->connector->execute($channel, $action);
    }

    /**
     * @param Shopware6Channel $channel
     * @param BatchVisibilitiesProduct $visibilitiesProduct
     * @param Shopware6Language|null $shopware6Language
     * @return void
     * @throws Exception
     */
    public function delete(
        Shopware6Channel $channel,
        BatchVisibilitiesProduct $visibilitiesProduct,
        ?Shopware6Language $shopware6Language = null
    ): void {
        $action = new BatchDeleteProductVisiblity($visibilitiesProduct);
        if ($shopware6Language) {
            $action->addHeader('sw-language-id', $shopware6Language->getId());
        }
        $this->connector->execute($channel, $action);
    }
}
