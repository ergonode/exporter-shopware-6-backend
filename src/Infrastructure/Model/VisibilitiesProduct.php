<?php

/*
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Model;

class VisibilitiesProduct implements \JsonSerializable
{
    private ?string $id;

    private ?string $productId;

    private ?string $salesChannelId;

    private ?int $visibility;

    public function __construct(
        ?string $id = null,
        ?string $productId = null,
        ?string $salesChannelId = null,
        ?int $visibility = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->salesChannelId = $salesChannelId;
        $this->visibility = $visibility;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        if (null !== $this->id) {
            $data['id'] = $this->id;
        }
        if (null !== $this->productId) {
            $data['productId'] = $this->productId;
        }
        if (null !== $this->salesChannelId) {
            $data['salesChannelId'] = $this->salesChannelId;
        }
        if (null !== $this->visibility) {
            $data['visibility'] = $this->visibility;
        }

        return $data;
    }
}
