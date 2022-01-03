<?php
declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Infrastructure\Processor\Process;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Channel\Domain\Entity\Export;
use Ergonode\Channel\Domain\Repository\ExportRepositoryInterface;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Domain\Repository\CustomFieldRepositoryInterface;
use Ergonode\ExporterShopware6\Infrastructure\Builder\CustomFieldBuilder;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6CustomFieldClient;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6CustomFieldSetClient;
use Ergonode\ExporterShopware6\Infrastructure\Exception\Shopware6ExporterException;
use Ergonode\ExporterShopware6\Infrastructure\Model\AbstractShopware6CustomFieldSet;
use Ergonode\ExporterShopware6\Infrastructure\Model\Basic\Shopware6CustomField;
use Ergonode\ExporterShopware6\Infrastructure\Model\Basic\Shopware6CustomFieldSet;
use Ergonode\ExporterShopware6\Infrastructure\Model\Basic\Shopware6CustomFieldSetConfig;
use Ergonode\ExporterShopware6\Infrastructure\Model\CustomField\BatchCustomField;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Webmozart\Assert\Assert;

class CustomFieldShopware6ExportProcess
{
    private const CUSTOM_FIELD_SET_NAME = 'ergonode';

    protected AttributeRepositoryInterface $attributeRepository;

    private CustomFieldRepositoryInterface $customFieldRepository;

    private Shopware6CustomFieldClient $customFieldClient;

    private CustomFieldBuilder $builder;

    private Shopware6CustomFieldSetClient $customFieldSetClient;

    private ExportRepositoryInterface $exportRepository;

    public function __construct(
        CustomFieldRepositoryInterface $customFieldRepository,
        Shopware6CustomFieldClient $customFieldClient,
        CustomFieldBuilder $builder,
        Shopware6CustomFieldSetClient $customFieldSetClient,
        ExportRepositoryInterface $exportRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldClient = $customFieldClient;
        $this->builder = $builder;
        $this->customFieldSetClient = $customFieldSetClient;
        $this->exportRepository = $exportRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Export $export
     * @param Shopware6Channel $channel
     * @param AttributeId[] $attributeIds
     * @throws \Exception
     */
    public function process(
        Export $export,
        Shopware6Channel $channel,
        array $attributeIds = []
    ): void {
        $shopwareCustomFields = $this->customFieldClient->getAll($channel);
        $customFieldSet = $this->loadCustomFieldSet($channel);

        $customFields = [];
        foreach ($attributeIds as $attributeId) {
            $attribute = $this->attributeRepository->load($attributeId);
            Assert::isInstanceOf($attribute, AbstractAttribute::class);
            $shopwareId = $this->customFieldRepository->load($channel->getId(), $attributeId);

            $customField = ($shopwareId && isset($shopwareCustomFields[$shopwareId])) ? $shopwareCustomFields[$shopwareId] : null;
            if (!$customField) {
                foreach ($shopwareCustomFields as $id => $shopwareCustomField) {
                    if ($shopwareCustomField->getName() === $attribute->getCode()->getValue()) {
                        $customField = $shopwareCustomFields[$id];
                        break;
                    }
                }
                if (!$customField) {
                    $customField = new Shopware6CustomField();
                }
            }

            $this->builder->build($channel, $export, $customField, $attribute);
            if ($customField->getCustomFieldSetId() === null) {
                $customField->setCustomFieldSetId($customFieldSet->getId());
            }

            $customField->setRequestName(sprintf('%s_%s', $attributeId->getValue(), $attribute->getType()));
            $customFields[] = $customField;
        }

        try {
            $this->customFieldClient->insertBatch($channel, new BatchCustomField($customFields));
        } catch (Shopware6ExporterException $exception) {
            $this->exportRepository->addError(
                $export->getId(),
                $exception->getMessage(),
                $exception->getParameters()
            );
        }
    }

    private function loadCustomFieldSet(
        Shopware6Channel $channel
    ): AbstractShopware6CustomFieldSet {
        $customFieldSet = $this->customFieldSetClient->findByCode($channel, self::CUSTOM_FIELD_SET_NAME);
        if ($customFieldSet) {
            return $customFieldSet;
        }
        $label = [
            str_replace('_', '-', $channel->getDefaultLanguage()->getCode()) => self::CUSTOM_FIELD_SET_NAME,
        ];

        $config = new Shopware6CustomFieldSetConfig(
            true,
            $label
        );

        $customFieldSet = new Shopware6CustomFieldSet(
            null,
            self::CUSTOM_FIELD_SET_NAME,
            $config,
            [
                [
                    'entityName' => 'product',
                ],
            ]
        );
        $newCustomFieldSet = $this->customFieldSetClient->insert($channel, $customFieldSet);
        Assert::notNull($newCustomFieldSet);

        return $newCustomFieldSet;
    }
}
