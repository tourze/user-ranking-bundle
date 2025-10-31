<?php

namespace UserRankingBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingListRepository;

/**
 * @internal
 */
#[CoversClass(UserRankingListRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingListRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRankingListRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getService(UserRankingListRepository::class);
        $this->assertInstanceOf(UserRankingListRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingListRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistList(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($list);
        $entityManager->flush();

        // 验证保存成功
        $found = $this->repository->find($list->getId());
        $this->assertNotNull($found);
        $this->assertInstanceOf(UserRankingList::class, $found);
        $this->assertEquals('Test List', $found->getTitle());
        $this->assertTrue($found->isValid());
    }

    public function testFindValidLists(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建有效的排行榜
        $validList = new UserRankingList();
        $validList->setTitle('Valid List');
        $validList->setValid(true);
        $validList->setRefreshFrequency(RefreshFrequency::DAILY);

        // 创建无效的排行榜
        $invalidList = new UserRankingList();
        $invalidList->setTitle('Invalid List');
        $invalidList->setValid(false);
        $invalidList->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($validList);
        $entityManager->persist($invalidList);
        $entityManager->flush();

        // 查找有效的排行榜
        $validLists = $this->repository->findBy(['valid' => true]);
        $this->assertNotEmpty($validLists);

        foreach ($validLists as $list) {
            $this->assertInstanceOf(UserRankingList::class, $list);
            $this->assertTrue($list->isValid());
        }
    }

    public function testFindOneByWithNullCriteriaShouldReturnEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建一个 subtitle 为 null 的实体
        $list = new UserRankingList();
        $list->setTitle('List with Null Subtitle');
        $list->setSubtitle(null);
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($list);
        $entityManager->flush();

        $foundList = $this->repository->findOneBy(['subtitle' => null]);

        $this->assertInstanceOf(UserRankingList::class, $foundList);
        $this->assertNull($foundList->getSubtitle());
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Saved List');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $this->repository->save($list);

        // 验证实体已保存
        $this->assertNotNull($list->getId());

        $foundList = $this->repository->find($list->getId());
        $this->assertInstanceOf(UserRankingList::class, $foundList);
        $this->assertEquals('Saved List', $foundList->getTitle());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建并保存实体
        $list = new UserRankingList();
        $list->setTitle('To Be Removed');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($list);
        $entityManager->flush();

        $listId = $list->getId();
        $this->assertNotNull($listId);

        // 删除实体
        $this->repository->remove($list);

        // 验证实体已删除
        $foundList = $this->repository->find($listId);
        $this->assertNull($foundList);
    }

    /**
     * @return UserRankingList
     */
    protected function createNewEntity(): object
    {
        $entity = new UserRankingList();
        $entity->setTitle('Test List ' . uniqid());
        $entity->setRefreshFrequency(RefreshFrequency::DAILY);
        $entity->setScoreSql('SELECT 1 as score');

        return $entity;
    }

    protected function getRepository(): UserRankingListRepository
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
        $this->assertInstanceOf(UserRankingList::class, $foundEntity);
        $this->assertEquals($entity2->getId(), $foundEntity->getId());
        $this->assertEquals($entity2->getTitle(), $foundEntity->getTitle());
    }
}
