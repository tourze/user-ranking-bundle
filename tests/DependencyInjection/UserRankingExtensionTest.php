<?php

namespace UserRankingBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use UserRankingBundle\DependencyInjection\UserRankingExtension;

/**
 * @internal
 */
#[CoversClass(UserRankingExtension::class)]
final class UserRankingExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private UserRankingExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new UserRankingExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Command\ArchiveRankingCommand'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Command\BlacklistCleanupCommand'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Command\RankingCalculateCommand'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Command\RefreshListCommand'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Repository\UserRankingArchiveRepository'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Repository\UserRankingBlacklistRepository'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Repository\UserRankingItemRepository'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Repository\UserRankingListRepository'));
        $this->assertTrue($this->container->hasDefinition('UserRankingBundle\Repository\UserRankingPositionRepository'));
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('user_ranking', $this->extension->getAlias());
    }
}
