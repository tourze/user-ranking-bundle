<?php

namespace UserRankingBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Messenger\MessengerBundle;
use Symfony\Component\Security\Core\SecurityBundle;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\LockServiceBundle\LockServiceBundle;
use Tourze\SnowflakeBundle\SnowflakeBundle;
use Tourze\SymfonyAsyncBundle\SymfonyAsyncBundle;
use Tourze\SymfonyCronJobBundle\SymfonyCronJobBundle;
use UserRankingBundle\UserRankingBundle;

class IntegrationTestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new SecurityBundle();
        yield new MessengerBundle();
        
        // 依赖的 Bundle
        yield new SnowflakeBundle();
        yield new DoctrineSnowflakeBundle();
        yield new DoctrineIndexedBundle();
        yield new DoctrineTimestampBundle();
        yield new DoctrineTrackBundle();
        yield new DoctrineUserBundle();
        yield new LockServiceBundle();
        yield new SymfonyAsyncBundle();
        yield new SymfonyCronJobBundle();
        
        // 被测试的 Bundle
        yield new UserRankingBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // 基本框架配置
        $container->extension('framework', [
            'secret' => 'TEST_SECRET',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'messenger' => [
                'default_bus' => 'messenger.bus.default',
                'buses' => [
                    'messenger.bus.default' => [],
                ],
            ],
            'php_errors' => [
                'log' => true,
            ],
        ]);

        // Doctrine 配置 - 使用内存数据库
        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'controller_resolver' => [
                    'auto_mapping' => false,
                ],
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping' => true,
                'mappings' => [
                    'UserRankingBundle' => [
                        'is_bundle' => true,
                        'type' => 'attribute',
                        'dir' => 'src/Entity',
                        'prefix' => 'UserRankingBundle\Entity',
                    ],
                    'TestEntities' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Integration/Entity',
                        'prefix' => 'UserRankingBundle\Tests\Integration\Entity',
                    ],
                ],
            ],
        ]);
        
        // Snowflake 配置
        $container->extension('snowflake', [
            'node_id' => 1,
            'datacenter_id' => 1,
        ]);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }
} 