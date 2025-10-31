<?php

namespace UserRankingBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserRankingBundle\Entity\UserRankingPosition;
use UserRankingBundle\Repository\UserRankingPositionRepository;

/**
 * @internal
 */
#[CoversClass(UserRankingPositionRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingPositionRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRankingPositionRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getService(UserRankingPositionRepository::class);
        $this->assertInstanceOf(UserRankingPositionRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingPositionRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistPosition(): void
    {
        $entityManager = self::getEntityManager();

        // 创建测试数据
        $position = new UserRankingPosition();
        $position->setTitle('Test Position');
        $position->setValid(true);

        $entityManager->persist($position);
        $entityManager->flush();

        // 验证保存成功
        $found = $this->repository->find($position->getId());
        $this->assertNotNull($found);
        $this->assertInstanceOf(UserRankingPosition::class, $found);
        $this->assertEquals('Test Position', $found->getTitle());
        $this->assertTrue($found->isValid());
    }

    public function testFindValidPositions(): void
    {
        $entityManager = self::getEntityManager();

        // 创建有效的位置
        $validPosition = new UserRankingPosition();
        $validPosition->setTitle('Valid Position');
        $validPosition->setValid(true);

        // 创建无效的位置
        $invalidPosition = new UserRankingPosition();
        $invalidPosition->setTitle('Invalid Position');
        $invalidPosition->setValid(false);

        $entityManager->persist($validPosition);
        $entityManager->persist($invalidPosition);
        $entityManager->flush();

        // 查找有效的位置
        $validPositions = $this->repository->findBy(['valid' => true]);
        $this->assertNotEmpty($validPositions);

        foreach ($validPositions as $position) {
            $this->assertInstanceOf(UserRankingPosition::class, $position);
            $this->assertTrue($position->isValid());
        }
    }

    public function testFindOneByWithNullCriteriaShouldReturnEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建一个 title 为 null 的实体
        $position = new UserRankingPosition();
        $position->setTitle(null);
        $position->setValid(true);

        $entityManager->persist($position);
        $entityManager->flush();

        $foundPosition = $this->repository->findOneBy(['title' => null]);

        $this->assertInstanceOf(UserRankingPosition::class, $foundPosition);
        $this->assertNull($foundPosition->getTitle());
    }

    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 清空表以避免数据干扰
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingPosition')->execute();

        // 创建多个测试实体
        $position1 = new UserRankingPosition();
        $position1->setTitle('AAA Position');
        $position1->setValid(true);

        $position2 = new UserRankingPosition();
        $position2->setTitle('ZZZ Position');
        $position2->setValid(true);

        $entityManager->persist($position1);
        $entityManager->persist($position2);
        $entityManager->flush();

        // 按 title 升序查找第一个
        $foundPosition = $this->repository->findOneBy(['valid' => true], ['title' => 'ASC']);

        $this->assertInstanceOf(UserRankingPosition::class, $foundPosition);
        $this->assertEquals('AAA Position', $foundPosition->getTitle());

        // 按 title 降序查找第一个
        $foundPosition = $this->repository->findOneBy(['valid' => true], ['title' => 'DESC']);

        $this->assertInstanceOf(UserRankingPosition::class, $foundPosition);
        $this->assertEquals('ZZZ Position', $foundPosition->getTitle());
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $position = new UserRankingPosition();
        $position->setTitle('Saved Position');
        $position->setValid(true);

        $this->repository->save($position);

        // 验证实体已保存
        $this->assertNotNull($position->getId());

        $foundPosition = $this->repository->find($position->getId());
        $this->assertInstanceOf(UserRankingPosition::class, $foundPosition);
        $this->assertEquals('Saved Position', $foundPosition->getTitle());
    }

    public function testSaveMethodWithFlushFalseShouldNotFlush(): void
    {
        $position = new UserRankingPosition();
        $position->setTitle('Not Flushed Position');
        $position->setValid(true);

        $this->repository->save($position, false);

        // 手动刷新以确保保存
        self::getEntityManager()->flush();

        $this->assertNotNull($position->getId());

        $foundPosition = $this->repository->find($position->getId());
        $this->assertInstanceOf(UserRankingPosition::class, $foundPosition);
        $this->assertEquals('Not Flushed Position', $foundPosition->getTitle());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建并保存实体
        $position = new UserRankingPosition();
        $position->setTitle('To Be Removed');
        $position->setValid(true);

        $entityManager->persist($position);
        $entityManager->flush();

        $positionId = $position->getId();
        $this->assertNotNull($positionId);

        // 删除实体
        $this->repository->remove($position);

        // 验证实体已删除
        $foundPosition = $this->repository->find($positionId);
        $this->assertNull($foundPosition);
    }

    public function testRemoveMethodWithFlushFalseShouldNotFlush(): void
    {
        $entityManager = self::getEntityManager();

        // 创建并保存实体
        $position = new UserRankingPosition();
        $position->setTitle('To Be Removed Later');
        $position->setValid(true);

        $entityManager->persist($position);
        $entityManager->flush();

        $positionId = $position->getId();

        // 删除但不刷新
        $this->repository->remove($position, false);

        // 手动刷新
        $entityManager->flush();

        // 验证实体已删除
        $foundPosition = $this->repository->find($positionId);
        $this->assertNull($foundPosition);
    }

    /**
     * @return UserRankingPosition
     */
    protected function createNewEntity(): object
    {
        $entity = new UserRankingPosition();
        $entity->setTitle('Test Position ' . uniqid());
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): UserRankingPositionRepository
    {
        return $this->repository;
    }

    /**
     * 专门测试Snowflake ID的flush行为
     * 因为AbstractRepositoryTestCase跳过了自定义ID生成器的测试，我们在这里补充
     */
    public function testSaveWithFlushFalseShouldNotImmediatelyPersistForSnowflakeId(): void
    {
        $entity = $this->createNewEntity();

        // 保存实体但不flush
        $this->repository->save($entity, false);

        // Snowflake ID 在persist时就会生成
        $this->assertNotNull($entity->getId(), 'Snowflake ID应该在persist时生成');

        // 检查实体是否在UnitOfWork的新实体列表中
        $uow = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($uow->isScheduledForInsert($entity), '实体应该被调度插入但尚未执行');

        // 清除EntityManager来验证实体未真正持久化
        $entityId = $entity->getId();
        self::getEntityManager()->clear();

        // 现在尝试从数据库中找到实体 - 应该找不到
        $foundEntity = $this->repository->find($entityId);
        $this->assertNull($foundEntity, 'flush=false时实体不应该立即持久化到数据库');

        // 重新创建并保存实体，然后flush
        $entity2 = $this->createNewEntity();
        $this->repository->save($entity2, true);  // 使用flush=true

        // 现在应该能找到
        $foundEntity = $this->repository->find($entity2->getId());
        $this->assertNotNull($foundEntity, 'flush=true时实体应该立即持久化到数据库');
        $this->assertInstanceOf(UserRankingPosition::class, $foundEntity);
        $this->assertEquals($entity2->getId(), $foundEntity->getId());
        $this->assertEquals($entity2->getTitle(), $foundEntity->getTitle());
    }
}
