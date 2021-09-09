<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Application\Form\Liform\Transformer;

use Limenius\Liform\ResolverInterface;
use Limenius\Liform\Transformer\AbstractTransformer;
use Limenius\Liform\Transformer\ExtensionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RelationFormTransformer extends AbstractTransformer
{
    protected ResolverInterface $resolver;

    public function __construct(
        TranslatorInterface $translator,
        ResolverInterface $resolver,
        FormTypeGuesserInterface $validatorGuesser = null
    ) {
        parent::__construct($translator, $validatorGuesser);

        $this->resolver = $resolver;
    }

    /**
     * @param ExtensionInterface[] $extensions
     * @param string|null $widget
     */
    public function transform(FormInterface $form, array $extensions = [], $widget = null): array
    {
        $data = [];
        $order = 1;
        $required = [];

        foreach ($form->all() as $key => $field) {
            $transformerData = $this->resolver->resolve($field);
            $transformedChild = $transformerData['transformer']->transform(
                $field,
                $extensions,
                $transformerData['widget'],
            );
            $transformedChild['propertyOrder'] = $order;

            $object = [];
            $object['title'] = $field->getConfig()->getOption('label');
            $object['properties'][$key] = $transformedChild;

            $data[] = $object;
            $order++;

            if ($transformerData['transformer']->isRequired($field)) {
                $required[] = $field->getName();
            }
        }

        $schema = [
            'type' => 'object',
            'oneOf' => $data,
        ];

        if (!empty($required)) {
            $schema['required'] = $required;
        }

        $innerType = $form->getConfig()->getType()->getInnerType();

        $schema = $this->addCommonSpecs($form, $schema, $extensions, $widget);

        if (method_exists($innerType, 'buildLiform')) {
            $schema = $innerType->buildLiform($form, $schema);
        }

        return $schema;
    }
}
