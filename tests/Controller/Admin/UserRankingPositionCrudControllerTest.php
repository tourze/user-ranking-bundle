<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserRankingBundle\Controller\Admin\UserRankingPositionCrudController;
use UserRankingBundle\Entity\UserRankingPosition;

/**
 * UserRankingPositionCrudController 测试
 *
 * @internal
 */
#[CoversClass(UserRankingPositionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingPositionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingPositionCrudController::class);
        $this->assertInstanceOf(UserRankingPositionCrudController::class, $controller);
    }

    public function testEntityFqcnIsCorrect(): void
    {
        $this->assertSame(
            UserRankingPosition::class,
            UserRankingPositionCrudController::getEntityFqcn()
        );
    }

    public function testConfigureFieldsReturnsValidConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $controller = self::getService(UserRankingPositionCrudController::class);

        // 验证配置方法返回正确的类型
        $fields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $this->assertGreaterThan(3, count($fields), '应该配置多个字段');
    }

    protected function getControllerService(): UserRankingPositionCrudController
    {
        return self::getService(UserRankingPositionCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '名称' => ['名称'],
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
            'valid' => ['valid'],
            'lists' => ['lists'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        return [
            'title' => ['title'],
            'valid' => ['valid'],
            'lists' => ['lists'],
        ];
    }
}
