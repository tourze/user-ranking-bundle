<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserRankingBundle\Controller\Admin\UserRankingListCrudController;
use UserRankingBundle\Entity\UserRankingList;

/**
 * UserRankingListCrudController 测试
 *
 * @internal
 */
#[CoversClass(UserRankingListCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingListCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingListCrudController::class);
        $this->assertInstanceOf(UserRankingListCrudController::class, $controller);
    }

    public function testEntityFqcnIsCorrect(): void
    {
        $this->assertSame(
            UserRankingList::class,
            UserRankingListCrudController::getEntityFqcn()
        );
    }

    public function testConfigureFieldsReturnsValidConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingListCrudController::class);

        // 验证配置方法返回正确的类型
        $fields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(5, count($fields), '应该配置多个字段');
    }

    protected function getControllerService(): UserRankingListCrudController
    {
        return self::getService(UserRankingListCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '标题' => ['标题'],
            '副标题' => ['副标题'],
            '颜色' => ['颜色'],
            'LOGO地址' => ['LOGO地址'],
            '开始时间' => ['开始时间'],
            '结束时间' => ['结束时间'],
            '总名次' => ['总名次'],
            '更新频率' => ['更新频率'],
            '最后刷新时间' => ['最后刷新时间'],
            '是否有效' => ['是否有效'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return [
            'title' => ['title'],
            'subtitle' => ['subtitle'],
            'color' => ['color'],
            'logoUrl' => ['logoUrl'],
            'startTime' => ['startTime'],
            'endTime' => ['endTime'],
            'scoreSql' => ['scoreSql'],
            'positions' => ['positions'],
            'refreshFrequency' => ['refreshFrequency'],
            'valid' => ['valid'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        return [
            'title' => ['title'],
            'subtitle' => ['subtitle'],
            'color' => ['color'],
            'logoUrl' => ['logoUrl'],
            'startTime' => ['startTime'],
            'endTime' => ['endTime'],
            'scoreSql' => ['scoreSql'],
            'positions' => ['positions'],
            'refreshFrequency' => ['refreshFrequency'],
            'valid' => ['valid'],
        ];
    }
}
