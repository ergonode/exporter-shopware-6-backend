<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Application\Model\Type;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductRelationModel
{
    /**
     * @var string[]
     */
    public array $crossSelling = [];

    /**
     * @var string[]
     */
    public array $relationAttributes = [];

    public function __construct(array $crossSelling = [], array $relationAttributes = [])
    {
        $this->crossSelling = $crossSelling;
        $this->relationAttributes = $relationAttributes;
    }

    /**
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (!empty($this->crossSelling) && !empty($this->relationAttributes)) {
            $context->buildViolation('Only one of relation types should be used')
                ->atPath('crossSelling')
                ->addViolation();

            $context->buildViolation('Only one of relation types should be used')
                ->atPath('relationAttributes')
                ->addViolation();
        }
    }
}
