<?php

namespace UserRankingBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingItemRepository;

/**
 * @internal
 */
#[CoversClass(UserRankingItemRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingItemRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRankingItemRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getService(UserRankingItemRepository::class);
        $this->assertInstanceOf(UserRankingItemRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingItemRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistItem(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($list);

        $item = new UserRankingItem();
        $item->setList($list);
        $item->setNumber(1);
        $item->setUserId('123');
        $item->setScore(100);
        $item->setValid(true);
        $item->setFixed(false);

        $entityManager->persist($item);
        $entityManager->flush();

        // 验证保存成功
        $found = $this->repository->find($item->getId());
        $this->assertNotNull($found);
        $this->assertInstanceOf(UserRankingItem::class, $found);
        $this->assertEquals(1, $found->getNumber());
        $this->assertEquals('123', $found->getUserId());
        $this->assertEquals(100, $found->getScore());
        $this->assertTrue($found->isValid());
        $this->assertFalse($found->isFixed());
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);
        $entityManager->flush();

        $item = new UserRankingItem();
        $item->setList($list);
        $item->setNumber(1);
        $item->setUserId('saved_user');
        $item->setScore(100);
        $item->setValid(true);
        $item->setFixed(false);

        $this->repository->save($item);

        // 验证实体已保存
        $this->assertNotNull($item->getId());

        $foundItem = $this->repository->find($item->getId());
        $this->assertInstanceOf(UserRankingItem::class, $foundItem);
        $this->assertEquals('saved_user', $foundItem->getUserId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        // 创建并保存实体
        $item = new UserRankingItem();
        $item->setList($list);
        $item->setNumber(1);
        $item->setUserId('to_be_removed');
        $item->setScore(100);
        $item->setValid(true);
        $item->setFixed(false);

        $entityManager->persist($item);
        $entityManager->flush();

        $itemId = $item->getId();
        $this->assertNotNull($itemId);

        // 删除实体
        $this->repository->remove($item);

        // 验证实体已删除
        $foundItem = $this->repository->find($itemId);
        $this->assertNull($foundItem);
    }

    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingItem')->execute();

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        // 创建多个测试实体
        $item1 = new UserRankingItem();
        $item1->setList($list);
        $item1->setNumber(1);
        $item1->setUserId('aaa_user');
        $item1->setScore(100);
        $item1->setValid(true);
        $item1->setFixed(false);

        $item2 = new UserRankingItem();
        $item2->setList($list);
        $item2->setNumber(2);
        $item2->setUserId('zzz_user');
        $item2->setScore(90);
        $item2->setValid(true);
        $item2->setFixed(false);

        $entityManager->persist($item1);
        $entityManager->persist($item2);
        $entityManager->flush();

        // 按 userId 升序查找第一个
        $foundItem = $this->repository->findOneBy(['valid' => true], ['userId' => 'ASC']);

        $this->assertInstanceOf(UserRankingItem::class, $foundItem);
        $this->assertEquals('aaa_user', $foundItem->getUserId());

        // 按 userId 降序查找第一个
        $foundItem = $this->repository->findOneBy(['valid' => true], ['userId' => 'DESC']);

        $this->assertInstanceOf(UserRankingItem::class, $foundItem);
        $this->assertEquals('zzz_user', $foundItem->getUserId());
    }

    public function testCountByAssociationListShouldReturnCorrectNumber(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingItem')->execute();

        // 创建两个排行榜
        $list1 = new UserRankingList();
        $list1->setTitle('排行槜1');
        $list1->setValid(true);
        $list1->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list1);

        $list2 = new UserRankingList();
        $list2->setTitle('排行槜2');
        $list2->setValid(true);
        $list2->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list2);

        // 为第一个排行榜创建3个排行项
        for ($i = 1; $i <= 3; ++$i) {
            $item = new UserRankingItem();
            $item->setList($list1);
            $item->setNumber($i);
            $item->setUserId('list1_user' . $i);
            $item->setScore(100 - $i);
            $item->setValid(true);
            $item->setFixed(false);
            $entityManager->persist($item);
        }

        // 为第二个排行榜创建2个排行项
        for ($i = 1; $i <= 2; ++$i) {
            $item = new UserRankingItem();
            $item->setList($list2);
            $item->setNumber($i);
            $item->setUserId('list2_user' . $i);
            $item->setScore(80 - $i);
            $item->setValid(true);
            $item->setFixed(false);
            $entityManager->persist($item);
        }
        $entityManager->flush();

        $count = $this->repository->count(['list' => $list1]);
        $this->assertSame(3, $count);
    }

    public function testFindOneByWithNullTextReasonShouldReturnEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        // 创建一个 textReason 为 null 的实体
        $item = new UserRankingItem();
        $item->setList($list);
        $item->setNumber(1);
        $item->setUserId('null_reason_user');
        $item->setScore(100);
        $item->setValid(true);
        $item->setFixed(false);
        $item->setTextReason(null);

        $entityManager->persist($item);
        $entityManager->flush();

        $foundItem = $this->repository->findOneBy(['textReason' => null]);

        $this->assertInstanceOf(UserRankingItem::class, $foundItem);
        $this->assertNull($foundItem->getTextReason());
    }

    public function testFindOneByAssociationListShouldReturnMatchingEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingItem')->execute();

        // 创建两个排行榜
        $list1 = new UserRankingList();
        $list1->setTitle('排行榜1');
        $list1->setValid(true);
        $list1->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list1);

        $list2 = new UserRankingList();
        $list2->setTitle('排行榜2');
        $list2->setValid(true);
        $list2->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list2);

        // 为排行榜创建项目
        $item1 = new UserRankingItem();
        $item1->setList($list1);
        $item1->setNumber(1);
        $item1->setUserId('list1_user');
        $item1->setScore(100);
        $item1->setValid(true);
        $item1->setFixed(false);

        $item2 = new UserRankingItem();
        $item2->setList($list2);
        $item2->setNumber(1);
        $item2->setUserId('list2_user');
        $item2->setScore(90);
        $item2->setValid(true);
        $item2->setFixed(false);

        $entityManager->persist($item1);
        $entityManager->persist($item2);
        $entityManager->flush();

        $foundItem = $this->repository->findOneBy(['list' => $list1]);

        $this->assertInstanceOf(UserRankingItem::class, $foundItem);
        $this->assertSame($list1, $foundItem->getList());
        $this->assertEquals('list1_user', $foundItem->getUserId());
    }

    /**
     * @return UserRankingItem
     */
    protected function createNewEntity(): object
    {
        $list = new UserRankingList();
        $list->setTitle('Test List ' . uniqid());
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $list->setScoreSql('SELECT 1 as score');

        $entity = new UserRankingItem();
        $entity->setList($list);
        $entity->setNumber(1);
        $entity->setUserId('test_user_' . uniqid());
        $entity->setScore(100);

        return $entity;
    }

    protected function getRepository(): UserRankingItemRepository
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
        $this->assertInstanceOf(UserRankingItem::class, $foundEntity);
        $this->assertEquals($entity2->getId(), $foundEntity->getId());
        $this->assertEquals($entity2->getUserId(), $foundEntity->getUserId());
    }
}
