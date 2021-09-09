<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Connector;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use GuzzleHttp\ClientInterface;

interface ClientFactoryInterface
{
    public function create(Shopware6Channel $channel): ClientInterface;
}
