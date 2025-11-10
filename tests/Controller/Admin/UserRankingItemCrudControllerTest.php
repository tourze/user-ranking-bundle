<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserRankingBundle\Controller\Admin\UserRankingItemCrudController;
use UserRankingBundle\Entity\UserRankingItem;

/**
 * UserRankingItemCrudController 测试
 *
 * @internal
 */
#[CoversClass(UserRankingItemCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingItemCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingItemCrudController::class);
        $this->assertInstanceOf(UserRankingItemCrudController::class, $controller);
    }

    public function testEntityFqcnIsCorrect(): void
    {
        $this->assertSame(
            UserRankingItem::class,
            UserRankingItemCrudController::getEntityFqcn()
        );
    }

    public function testConfigureFieldsReturnsValidConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingItemCrudController::class);

        // 验证配置方法返回正确的类型
        $fields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $this->assertGreaterThan(5, count($fields), '应该配置多个字段');
    }

    protected function getControllerService(): UserRankingItemCrudController
    {
        return self::getService(UserRankingItemCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '排行榜' => ['排行榜'],
            '排名' => ['排名'],
            '用户ID' => ['用户ID'],
            '分数' => ['分数'],
            '固定排名' => ['固定排名'],
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
            'number' => ['number'],
            'userId' => ['userId'],
            'textReason' => ['textReason'],
            'score' => ['score'],
            'fixed' => ['fixed'],
            'recommendThumb' => ['recommendThumb'],
            'recommendReason' => ['recommendReason'],
            'valid' => ['valid'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        return [
            'list' => ['list'],
            'number' => ['number'],
            'userId' => ['userId'],
            'textReason' => ['textReason'],
            'score' => ['score'],
            'fixed' => ['fixed'],
            'recommendThumb' => ['recommendThumb'],
            'recommendReason' => ['recommendReason'],
            'valid' => ['valid'],
        ];
    }
}
