<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6ProductMediaClient;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\ProductMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductMedia;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Multimedia\Domain\Repository\MultimediaRepositoryInterface;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Infrastructure\Calculator\TranslationInheritanceCalculator;
use Ergonode\SharedKernel\Domain\Aggregate\MultimediaId;
use Webmozart\Assert\Assert;

class ProductGalleryMapper implements ProductMapperInterface
{
    private AttributeRepositoryInterface $repository;

    private TranslationInheritanceCalculator $calculator;

    private MultimediaRepositoryInterface $multimediaRepository;

    private Shopware6ProductMediaClient $mediaClient;

    public function __construct(
        AttributeRepositoryInterface $repository,
        TranslationInheritanceCalculator $calculator,
        MultimediaRepositoryInterface $multimediaRepository,
        Shopware6ProductMediaClient $mediaClient
    ) {
        $this->repository = $repository;
        $this->calculator = $calculator;
        $this->multimediaRepository = $multimediaRepository;
        $this->mediaClient = $mediaClient;
    }

    public function map(
        Shopware6Channel $channel,
        Export $export,
        Shopware6Product $shopware6Product,
        AbstractProduct $product,
        ?Language $language = null
    ): Shopware6Product {
        if (null === $channel->getAttributeProductGallery()) {
            return $shopware6Product;
        }
        $attribute = $this->repository->load($channel->getAttributeProductGallery());

        Assert::notNull($attribute);

        if (false === $product->hasAttribute($attribute->getCode())) {
            return $shopware6Product;
        }

        $value = $product->getAttribute($attribute->getCode());
        $calculateValue = $this->calculator->calculate(
            $attribute->getScope(),
            $value,
            $language ?: $channel->getDefaultLanguage(),
        );
        if (is_array($calculateValue)) {
            $position = 0;
            foreach ($calculateValue as $galleryValue) {
                $multimediaId = new MultimediaId($galleryValue);
                $this->getShopware6MultimediaId($multimediaId, $shopware6Product, $channel, $position++);
            }
        }

        return $shopware6Product;
    }

    private function getShopware6MultimediaId(
        MultimediaId $multimediaId,
        Shopware6Product $shopware6Product,
        Shopware6Channel $channel,
        int $position
    ): Shopware6Product {
        $multimedia = $this->multimediaRepository->load($multimediaId);
        if ($multimedia) {
            $shopwareId = $this->mediaClient->findOrCreateMedia($channel, $multimedia);
            if ($shopwareId) {
                $shopware6Product->addMedia(new Shopware6ProductMedia(null, $shopwareId, $position));
            }
        }

        return $shopware6Product;
    }
}
