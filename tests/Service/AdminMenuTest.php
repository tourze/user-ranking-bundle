<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use UserRankingBundle\Service\AdminMenu;

/**
 * AdminMenu服务测试
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup for AdminMenu tests
    }

    public function testInvokeAddsMenuItems(): void
    {
        $container = self::getContainer();
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        $adminMenu($rootItem);

        // 验证菜单结构
        $businessMenu = $rootItem->getChild('业务管理');
        self::assertNotNull($businessMenu);

        $rankingMenu = $businessMenu->getChild('排行榜管理');
        self::assertNotNull($rankingMenu);

        // 验证排行榜管理子菜单项
        $listMenu = $rankingMenu->getChild('排行榜列表');
        self::assertNotNull($listMenu);

        $itemMenu = $rankingMenu->getChild('排行榜项目');
        self::assertNotNull($itemMenu);

        $positionMenu = $rankingMenu->getChild('职位管理');
        self::assertNotNull($positionMenu);

        $blacklistMenu = $rankingMenu->getChild('黑名单管理');
        self::assertNotNull($blacklistMenu);

        $archiveMenu = $rankingMenu->getChild('历史归档');
        self::assertNotNull($archiveMenu);
    }

    public function testCanBeInstantiated(): void
    {
        $container = self::getContainer();
        $adminMenu = $container->get(AdminMenu::class);

        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }
}
