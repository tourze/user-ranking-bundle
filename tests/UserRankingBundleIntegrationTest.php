<?php

namespace UserRankingBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UserRankingBundle\UserRankingBundle;

/**
 * @internal
 */
#[CoversClass(UserRankingBundle::class)]
final class UserRankingBundleIntegrationTest extends TestCase
{
    public function testGetBundleName(): void
    {
        $bundle = new UserRankingBundle();
        $this->assertEquals('UserRankingBundle', $bundle->getName());
    }

    public function testBundleRegistration(): void
    {
        $bundle = new UserRankingBundle();
        $container = new ContainerBuilder();

        $method = new \ReflectionMethod(UserRankingBundle::class, 'build');
        $method->setAccessible(true);
        $method->invoke($bundle, $container);

        // 验证容器中没有异常，Bundle构建成功
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}
