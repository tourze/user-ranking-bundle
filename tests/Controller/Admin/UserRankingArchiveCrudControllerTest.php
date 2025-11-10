<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserRankingBundle\Controller\Admin\UserRankingArchiveCrudController;
use UserRankingBundle\Entity\UserRankingArchive;

/**
 * UserRankingArchiveCrudController 测试
 *
 * @internal
 */
#[CoversClass(UserRankingArchiveCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingArchiveCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingArchiveCrudController::class);
        $this->assertInstanceOf(UserRankingArchiveCrudController::class, $controller);
    }

    public function testEntityFqcnIsCorrect(): void
    {
        $this->assertSame(
            UserRankingArchive::class,
            UserRankingArchiveCrudController::getEntityFqcn()
        );
    }

    public function testConfigureFieldsReturnsValidConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingArchiveCrudController::class);

        // 验证配置方法返回正确的类型
        $fields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $this->assertGreaterThan(5, count($fields), '应该配置多个字段');
    }

    protected function getControllerService(): UserRankingArchiveCrudController
    {
        return self::getService(UserRankingArchiveCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'id' => ['ID'],
            'list' => ['排行榜'],
            'number' => ['排名'],
            'userId' => ['用户ID'],
            'score' => ['分数'],
            'archiveTime' => ['归档时间'],
            'createTime' => ['创建时间'],
            'updateTime' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return [
            'list' => ['list'],
            'number' => ['number'],
            'userId' => ['userId'],
            'score' => ['score'],
            'archiveTime' => ['archiveTime'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        return [
            'list' => ['list'],
            'number' => ['number'],
            'userId' => ['userId'],
            'score' => ['score'],
            'archiveTime' => ['archiveTime'],
        ];
    }
}
