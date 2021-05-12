<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Application\Model\Type;

class ProductRelationModel
{
    /**
     * @var array
     */
    public array $crossSelling = [];

    public array $relations = [];

    public function __construct(array $crossSelling = [])
    {
        $this->crossSelling = $crossSelling;
    }
}
