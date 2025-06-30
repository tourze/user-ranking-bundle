<?php

namespace UserRankingBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UserRankingBundle\DependencyInjection\UserRankingExtension;
use UserRankingBundle\UserRankingBundle;

class UserRankingBundleTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new UserRankingBundle();
        $this->assertInstanceOf(UserRankingBundle::class, $bundle);
    }

    public function testGetContainerExtension(): void
    {
        $bundle = new UserRankingBundle();
        $extension = $bundle->getContainerExtension();
        
        $this->assertInstanceOf(UserRankingExtension::class, $extension);
    }

    public function testBuild(): void
    {
        $bundle = new UserRankingBundle();
        $container = new ContainerBuilder();
        
        // 测试 build 方法不抛出异常
        $bundle->build($container);
        $this->assertTrue(true); // 如果没有异常，测试通过
    }
}