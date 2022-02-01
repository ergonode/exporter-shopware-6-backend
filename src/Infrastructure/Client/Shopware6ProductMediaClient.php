<?php

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Client;

use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\MultimediaRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\DeleteMedia;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\GetMediaByFilename;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\GetMediaDefaultFolderList;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\HasMedia;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\PostCreateMediaAction;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Action\Media\PostUploadFile;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6Connector;
use Ergonode\ExporterShopware6\Infrastructure\Connector\Shopware6QueryBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6DefaultFolderException;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6InstanceOfException;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Media;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6MediaDefaultFolder;
use Ergonode\Multimedia\Domain\Entity\Multimedia;
use Ergonode\SharedKernel\Domain\Aggregate\MultimediaId;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use League\Flysystem\FilesystemInterface;

class Shopware6ProductMediaClient
{
    private Shopware6Connector $connector;

    private FilesystemInterface $multimediaStorage;

    private MultimediaRepositoryInterface $multimediaRepository;

    private array $inMemoryCache = [];

    public function __construct(
        Shopware6Connector $connector,
        FilesystemInterface $multimediaStorage,
        MultimediaRepositoryInterface $multimediaRepository
    ) {
        $this->connector = $connector;
        $this->multimediaStorage = $multimediaStorage;
        $this->multimediaRepository = $multimediaRepository;
    }

    /**
     * @throws Shopware6DefaultFolderException
     * @throws \Exception
     */
    public function findOrCreateMedia(Shopware6Channel $channel, Multimedia $multimedia): string
    {
        $shopwareId = $this->check($channel, $multimedia);
        if ($shopwareId) {
            return $shopwareId;
        }

        $folder = $this->getProductFolderId($channel);
        if (null === $folder) {
            throw new Shopware6DefaultFolderException();
        }

        $this->checkAndDeleteByFilename($channel, $multimedia);
        $media = $this->createNew($channel, $multimedia, $folder);

        return $media->getId();
    }

    /**
     * @throws \Exception
     */
    private function createNew(
        Shopware6Channel $channel,
        Multimedia $multimedia,
        Shopware6MediaDefaultFolder $folder
    ): Shopware6Media {
        $media = null;
        try {
            $media = $this->createMediaResource($channel, $folder);
            $this->upload($channel, $media, $multimedia);
            $this->multimediaRepository->save($channel->getId(), $multimedia->getId(), $media->getId());

            $this->inMemoryCache['has-media'][$channel->getHost()][$media->getId()] = true;
            return $media;
        } catch (\Exception $exception) {
            if ($media) {
                $this->delete($channel, $media->getId(), $multimedia->getId());
            }
            throw $exception;
        }
    }

    private function upload(Shopware6Channel $channel, Shopware6Media $media, Multimedia $multimedia): void
    {
        $content = $this->multimediaStorage->read($multimedia->getFileName());
        $name = $multimedia->getHash()->getValue();
        $iteration = 0;
        while (true) {
            try {
                $action = new PostUploadFile($media->getId(), $content, $multimedia, $name);
                $this->connector->execute($channel, $action);

                return;
            } catch (ServerException $exception) {
                $decode = json_decode(
                    $exception->getResponse()->getBody()->getContents(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if ($decode['errors'][0]['code'] !== 'CONTENT__MEDIA_DUPLICATED_FILE_NAME') {
                    throw $exception;
                }
                $name = $multimedia->getHash()->getValue() . '_' . $iteration++;
            }
        }
    }

    /**
     * @throws Shopware6InstanceOfException
     */
    private function createMediaResource(
        Shopware6Channel $channel,
        Shopware6MediaDefaultFolder $folder
    ): Shopware6Media {
        $action = new PostCreateMediaAction($folder->getMediaFolderId(), true);

        $shopware6Media = $this->connector->execute($channel, $action);
        if (!$shopware6Media instanceof Shopware6Media) {
            throw new Shopware6InstanceOfException(Shopware6Media::class);
        }

        return $shopware6Media;
    }

    private function getProductFolderId(Shopware6Channel $channel): ?Shopware6MediaDefaultFolder
    {
        if (isset($this->inMemoryCache['product-folder-id'][$channel->getId()])) {
            return $this->inMemoryCache['product-folder-id'][$channel->getId()];
        }

        $query = new Shopware6QueryBuilder();
        $query->equals('entity', 'product');

        $action = new GetMediaDefaultFolderList($query);

        $productFolderId = null;
        $folderList = $this->connector->execute($channel, $action);
        if (is_array($folderList) && count($folderList) > 0) {
            $productFolderId = reset($folderList);
            $this->inMemoryCache['product-folder-id'][$channel->getId()] = $productFolderId;
        }

        return $productFolderId;
    }

    private function check(Shopware6Channel $channel, Multimedia $multimedia): ?string
    {
        if (!$this->multimediaRepository->exists($channel->getId(), $multimedia->getId())) {
            return null;
        }
        $shopwareId = $this->multimediaRepository->load($channel->getId(), $multimedia->getId());
        $mediaExist = $this->hasMedia($channel, $shopwareId);
        if (!$mediaExist) {
            return null;
        }

        return $shopwareId;
    }

    /**
     * @throws Shopware6InstanceOfException
     */
    private function hasMedia(Shopware6Channel $channel, string $shopwareId): bool
    {
        $action = new HasMedia($shopwareId);

        $channelHost = $channel->getHost();
        if (isset($this->inMemoryCache['has-media'][$channelHost][$shopwareId])) {
            return $this->inMemoryCache['has-media'][$channelHost][$shopwareId];
        }

        $hasMedia = false;
        try {
            $shopware6MediaId = $this->connector->execute($channel, $action);
            if (!is_string($shopware6MediaId)) {
                throw new Shopware6InstanceOfException(Shopware6Media::class);
            }

            $hasMedia = true;
        } catch (ClientException $exception) {
        }

        $this->inMemoryCache['has-media'][$channelHost][$shopwareId] = $hasMedia;
        return $hasMedia;
    }

    private function delete(Shopware6Channel $channel, string $shopwareId, MultimediaId $multimediaId): void
    {
        try {
            $action = new DeleteMedia($shopwareId);
            $this->connector->execute($channel, $action);
        } catch (ClientException $exception) {
        }
        $this->multimediaRepository->delete($channel->getId(), $multimediaId);
    }

    private function getMediaByFilename(Shopware6Channel $channel, string $filename): ?string
    {
        $query = new Shopware6QueryBuilder();
        $query->equals('fileName', $filename)
            ->limit(1);

        $action = new GetMediaByFilename($query);

        try {
            $shopware6MediaId = $this->connector->execute($channel, $action);
            if (is_string($shopware6MediaId)) {
                return $shopware6MediaId;
            }
        } catch (ClientException $exception) {
        }

        return null;
    }

    private function checkAndDeleteByFilename(Shopware6Channel $channel, Multimedia $multimedia): void
    {
        $filename = rtrim($multimedia->getFileName(), sprintf('.%s', $multimedia->getExtension()));
        $shopwareId = $this->getMediaByFilename($channel, $filename);
        if ($shopwareId) {
            try {
                $action = new DeleteMedia($shopwareId);
                $this->connector->execute($channel, $action);
            } catch (ClientException $exception) {
            }
        }
    }
}
