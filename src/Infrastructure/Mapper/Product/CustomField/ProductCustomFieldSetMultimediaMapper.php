<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product\CustomField;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Domain\Entity\Attribute\AbstractImageAttribute;
use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Calculator\AttributeTranslationInheritanceCalculator;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6ProductMediaClient;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Mapper\Shopware6ExporterMultimediaException;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\Product\AbstractProductCustomFieldSetMapper;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Multimedia\Domain\Repository\MultimediaRepositoryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\MultimediaId;

class ProductCustomFieldSetMultimediaMapper extends AbstractProductCustomFieldSetMapper
{
    private MultimediaRepositoryInterface $multimediaRepository;

    private Shopware6ProductMediaClient $mediaClient;

    public function __construct(
        AttributeRepositoryInterface $repository,
        AttributeTranslationInheritanceCalculator $calculator,
        MultimediaRepositoryInterface $multimediaRepository,
        Shopware6ProductMediaClient $mediaClient
    ) {
        parent::__construct($repository, $calculator);
        $this->multimediaRepository = $multimediaRepository;
        $this->mediaClient = $mediaClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return AbstractImageAttribute::TYPE;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Shopware6ExporterMultimediaException
     */
    protected function getValue(
        Shopware6Channel $channel,
        AbstractAttribute $attribute,
        $calculateValue,
        Shopware6Product $shopware6Product = null
    ): string {
        $multimediaId = new MultimediaId($calculateValue);

        return $this->getShopware6MultimediaId($channel, $multimediaId, $shopware6Product, $attribute);
    }

    /**
     * @throws Shopware6ExporterMultimediaException
     */
    private function getShopware6MultimediaId(
        Shopware6Channel $channel,
        MultimediaId $multimediaId,
        Shopware6Product $shopware6Product,
        AbstractAttribute $attribute
    ): string {
        $multimedia = $this->multimediaRepository->load($multimediaId);
        if ($multimedia) {
            return $this->mediaClient->findOrCreateMedia($channel, $multimedia, $shopware6Product, $attribute);
        }
        throw new Shopware6ExporterMultimediaException($multimediaId);
    }
}
