<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Application\Form;

use Ergonode\Attribute\Domain\Query\AttributeQueryInterface;
use Ergonode\ExporterShopware6\Application\Model\Type\ProductRelationModel;
use Ergonode\Product\Domain\Entity\Attribute\ProductRelationAttribute;
use Ergonode\ProductCollection\Domain\Query\ProductCollectionQueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductRelationForm extends AbstractType
{
    private AttributeQueryInterface $attributeQuery;
    private ProductCollectionQueryInterface $productCollectionQuery;

    public function __construct(
        AttributeQueryInterface $attributeQuery,
        ProductCollectionQueryInterface $productCollectionQuery
    ) {
        $this->attributeQuery = $attributeQuery;
        $this->productCollectionQuery = $productCollectionQuery;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $relationAttributeDictionary = $this->attributeQuery->getDictionary([ProductRelationAttribute::TYPE]);
        $productCollectionDictionary = $this->productCollectionQuery->getDictionary();

        $builder
            ->add(
                'cross_selling',
                ChoiceType::class,
                [
                    'label' => 'List of Product Collections',
                    'choices' => array_flip($productCollectionDictionary),
                    'multiple' => true,
                    'property_path' => 'crossSelling',
                    'required' => false,
                ],
            ) ->add(
                'relation_attributes',
                ChoiceType::class,
                [
                    'label' => 'List of Product Relation Attributes',
                    'choices' => array_flip($relationAttributeDictionary),
                    'multiple' => true,
                    'property_path' => 'relationAttributes',
                    'required' => false,
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'exporter',
                'data_class' => ProductRelationModel::class,
                'allow_extra_fields' => true,
                'label' => 'Export settings',
            ],
        );
    }

    public function getBlockPrefix(): ?string
    {
        return 'shopware-relation';
    }
}
