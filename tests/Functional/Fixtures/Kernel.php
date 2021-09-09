<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ExporterShopware6\Tests\Functional\Fixtures;

use Ergonode\SharedKernel\Application\AbstractModule;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{yaml,yml}';

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        return [
            new \DAMA\DoctrineTestBundle\DAMADoctrineTestBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Ergonode\Account\ErgonodeAccountBundle(),
            new \Ergonode\Api\ErgonodeApiBundle(),
            new \Ergonode\Attribute\ErgonodeAttributeBundle(),
            new \Ergonode\Authentication\ErgonodeAuthenticationBundle(),
            new \Ergonode\BatchAction\ErgonodeBatchActionBundle(),
            new \Ergonode\Category\ErgonodeCategoryBundle(),
            new \Ergonode\Channel\ErgonodeChannelBundle(),
            new \Ergonode\Completeness\ErgonodeCompletenessBundle(),
            new \Ergonode\Condition\ErgonodeConditionBundle(),
            new \Ergonode\Core\ErgonodeCoreBundle(),
            new \Ergonode\Designer\ErgonodeDesignerBundle(),
            new \Ergonode\EventSourcing\ErgonodeEventSourcingBundle(),
            new \Ergonode\ExporterShopware6\ErgonodeExporterShopware6Bundle(),
            new \Ergonode\Fixture\ErgonodeFixtureBundle(),
            new \Ergonode\Grid\ErgonodeGridBundle(),
            new \Ergonode\Migration\ErgonodeMigrationBundle(),
            new \Ergonode\Multimedia\ErgonodeMultimediaBundle(),
            new \Ergonode\Product\ErgonodeProductBundle(),
            new \Ergonode\ProductCollection\ErgonodeProductCollectionBundle(),
            new \Ergonode\Segment\ErgonodeSegmentBundle(),
            new \Ergonode\Value\ErgonodeValueBundle(),
            new \Ergonode\Workflow\ErgonodeWorkflowBundle(),
            new \League\FlysystemBundle\FlysystemBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new \Limenius\LiformBundle\LimeniusLiformBundle(),
            new \Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        foreach ($this->getBundles() as $bundle) {
            if (!$bundle instanceof AbstractModule) {
                continue;
            }
            if (file_exists($bundle->getPath() . '/Application/Resources/config/')) {
                $routes->import(
                    $bundle->getPath() . '/Application/Resources/config/{routes}' . self::CONFIG_EXTS,
                    '/',
                    'glob',
                );
            }
            if (!file_exists($bundle->getPath() . '/Resources/config/')) {
                continue;
            }
            $routes->import($bundle->getPath() . '/Resources/config/{routes}' . self::CONFIG_EXTS, '/', 'glob');
        }

        $routes->import(__DIR__ . '/Resources/config/routes.yaml');
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/Resources/config/config.yaml');
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/../../../var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/../../../var/logs';
    }
}
