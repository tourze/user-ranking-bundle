<?php

namespace UserRankingBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use UserRankingBundle\Repository\UserRankingListRepository;
use UserRankingBundle\UserRankingBundle;

class UserRankingBundleIntegrationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            UserRankingBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        parent::setUp();
    }

    public function testBundleLoads(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // 验证 Bundle 已注册
        $this->assertTrue($container->has('kernel'));
        $kernel = $container->get('kernel');

        $bundleFound = false;
        foreach ($kernel->getBundles() as $bundle) {
            if ($bundle instanceof UserRankingBundle) {
                $bundleFound = true;
                break;
            }
        }

        $this->assertTrue($bundleFound, 'UserRankingBundle 未在内核中注册');
    }

    public function testRepositoriesAreRegistered(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // 验证存储库服务已注册
        $this->assertTrue($container->has(UserRankingListRepository::class));
        $repository = $container->get(UserRankingListRepository::class);
        $this->assertInstanceOf(UserRankingListRepository::class, $repository);
    }

    public function testCommandsAreRegistered(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        // 验证命令已注册 - 可以通过检查 console.command 标签来实现
        $commandLocator = $container->get('console.command_loader');
        $this->assertTrue($commandLocator->has('user-ranking:calculate'), '用户排行榜计算命令未注册');
        $this->assertTrue($commandLocator->has('user-ranking:blacklist:cleanup'), '用户排行榜黑名单清理命令未注册');
        $this->assertTrue($commandLocator->has('user-ranking:archive'), '用户排行榜归档命令未注册');
    }
}
