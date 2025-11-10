<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserRankingBundle\Controller\Admin\UserRankingBlacklistCrudController;
use UserRankingBundle\Entity\UserRankingBlacklist;

/**
 * UserRankingBlacklistCrudController 测试
 *
 * @internal
 */
#[CoversClass(UserRankingBlacklistCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingBlacklistCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingBlacklistCrudController::class);
        $this->assertInstanceOf(UserRankingBlacklistCrudController::class, $controller);
    }

    public function testEntityFqcnIsCorrect(): void
    {
        $this->assertSame(
            UserRankingBlacklist::class,
            UserRankingBlacklistCrudController::getEntityFqcn()
        );
    }

    public function testConfigureFieldsReturnsValidConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingBlacklistCrudController::class);

        // 验证配置方法返回正确的类型
        $fields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $this->assertGreaterThan(5, count($fields), '应该配置多个字段');
    }

    protected function getControllerService(): UserRankingBlacklistCrudController
    {
        return self::getService(UserRankingBlacklistCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '排行榜' => ['排行榜'],
            '用户ID' => ['用户ID'],
            '解封时间' => ['解封时间'],
            '是否有效' => ['是否有效'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return [
            'list' => ['list'],
            'userId' => ['userId'],
            'reason' => ['reason'],
            'unblockTime' => ['unblockTime'],
            'expireTime' => ['expireTime'],
            'comment' => ['comment'],
            'valid' => ['valid'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        return [
            'list' => ['list'],
            'userId' => ['userId'],
            'reason' => ['reason'],
            'unblockTime' => ['unblockTime'],
            'expireTime' => ['expireTime'],
            'comment' => ['comment'],
            'valid' => ['valid'],
        ];
    }
}
