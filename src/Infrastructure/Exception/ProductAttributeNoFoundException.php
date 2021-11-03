<?php

/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Exception;

use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6ExporterException;
use Ergonode\Product\Domain\ValueObject\Sku;

class ProductAttributeNoFoundException extends Shopware6ExporterException
{
    private const MESSAGE = 'Product {product} no found attribute {attribute}';

    public function __construct(Sku $sku, AttributeCode $code, \Throwable $previous = null)
    {
        parent::__construct(
            self::MESSAGE,
            [
                '{product}' => $sku->getValue(),
                '{attribute}' => $code->getValue(),
            ],
            $previous,
        );
    }
}
