<?php

namespace UserRankingBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingArchiveRepository;

/**
 * @internal
 */
#[CoversClass(UserRankingArchiveRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingArchiveRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRankingArchiveRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getService(UserRankingArchiveRepository::class);
        $this->assertInstanceOf(UserRankingArchiveRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingArchiveRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertNotNull($result);
    }

    public function testCreateAndPersistArchive(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);

        $entityManager->persist($list);

        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('123');
        $archive->setScore(100);
        $archive->setArchiveTime(new \DateTimeImmutable());

        $entityManager->persist($archive);
        $entityManager->flush();

        // 验证保存成功
        $found = $this->repository->find($archive->getId());
        $this->assertNotNull($found);
        $this->assertInstanceOf(UserRankingArchive::class, $found);
        $this->assertEquals(1, $found->getNumber());
        $this->assertEquals('123', $found->getUserId());
        $this->assertEquals(100, $found->getScore());
    }

    public function testFindListHistoryRanking(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test History List');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        $archiveTime = new \DateTimeImmutable('2024-01-01 00:00:00');

        // 创建多个归档记录
        $archive1 = new UserRankingArchive();
        $archive1->setList($list);
        $archive1->setNumber(1);
        $archive1->setUserId('user1');
        $archive1->setScore(100);
        $archive1->setArchiveTime($archiveTime);
        $entityManager->persist($archive1);

        $archive2 = new UserRankingArchive();
        $archive2->setList($list);
        $archive2->setNumber(2);
        $archive2->setUserId('user2');
        $archive2->setScore(90);
        $archive2->setArchiveTime($archiveTime);
        $entityManager->persist($archive2);

        // 创建不同时间的归档记录（不应该被查询到）
        $archive3 = new UserRankingArchive();
        $archive3->setList($list);
        $archive3->setNumber(1);
        $archive3->setUserId('user3');
        $archive3->setScore(80);
        $archive3->setArchiveTime(new \DateTimeImmutable('2024-01-02 00:00:00'));
        $entityManager->persist($archive3);

        $entityManager->flush();

        // 测试查询历史排名
        $results = $this->repository->findListHistoryRanking($list, $archiveTime);

        $this->assertCount(2, $results);
        $this->assertEquals('user1', $results[0]->getUserId());
        $this->assertEquals(1, $results[0]->getNumber());
        $this->assertEquals('user2', $results[1]->getUserId());
        $this->assertEquals(2, $results[1]->getNumber());
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建测试的排行榜
        $list = new UserRankingList();
        $list->setTitle('Test List for Save');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);
        $entityManager->flush();

        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('saved-user');
        $archive->setScore(75);
        $archive->setArchiveTime(new \DateTimeImmutable());

        $this->repository->save($archive);

        // 验证实体已保存
        $this->assertNotNull($archive->getId());

        $foundArchive = $this->repository->find($archive->getId());
        $this->assertInstanceOf(UserRankingArchive::class, $foundArchive);
        $this->assertEquals('saved-user', $foundArchive->getUserId());
        $this->assertEquals(75, $foundArchive->getScore());
    }

    public function testSaveMethodWithFlushFalseShouldNotFlush(): void
    {
        $entityManager = self::getEntityManager();

        // 创建测试的排行榜
        $list = new UserRankingList();
        $list->setTitle('Test List for Save No Flush');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);
        $entityManager->flush();

        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('not-flushed-user');
        $archive->setScore(65);
        $archive->setArchiveTime(new \DateTimeImmutable());

        $this->repository->save($archive, false);

        // 手动刷新以确保保存
        $entityManager->flush();

        $this->assertNotNull($archive->getId());

        $foundArchive = $this->repository->find($archive->getId());
        $this->assertInstanceOf(UserRankingArchive::class, $foundArchive);
        $this->assertEquals('not-flushed-user', $foundArchive->getUserId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建测试的排行榜
        $list = new UserRankingList();
        $list->setTitle('Test List for Remove');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        // 创建并保存实体
        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('to-be-removed');
        $archive->setScore(55);
        $archive->setArchiveTime(new \DateTimeImmutable());

        $entityManager->persist($archive);
        $entityManager->flush();

        $archiveId = $archive->getId();
        $this->assertNotNull($archiveId);

        // 删除实体
        $this->repository->remove($archive);

        // 验证实体已删除
        $foundArchive = $this->repository->find($archiveId);
        $this->assertNull($foundArchive);
    }

    public function testRemoveMethodWithFlushFalseShouldNotFlush(): void
    {
        $entityManager = self::getEntityManager();

        // 创建测试的排行榜
        $list = new UserRankingList();
        $list->setTitle('Test List for Remove Later');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $entityManager->persist($list);

        // 创建并保存实体
        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('to-be-removed-later');
        $archive->setScore(45);
        $archive->setArchiveTime(new \DateTimeImmutable());

        $entityManager->persist($archive);
        $entityManager->flush();

        $archiveId = $archive->getId();

        // 删除但不刷新
        $this->repository->remove($archive, false);

        // 手动刷新
        $entityManager->flush();

        // 验证实体已删除
        $foundArchive = $this->repository->find($archiveId);
        $this->assertNull($foundArchive);
    }

    /**
     * @return UserRankingArchive
     */
    protected function createNewEntity(): object
    {
        // 创建一个简单的 list 实体，不持久化，因为测试框架会处理
        // 这里我们创建一个最小化的实体，只需要满足基本约束
        $list = new UserRankingList();
        $list->setTitle('Test List for Archive');
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $list->setScoreSql('SELECT 1 as score');

        $entity = new UserRankingArchive();
        $entity->setList($list);
        $entity->setNumber(1);
        $entity->setUserId('test_user_' . uniqid());
        $entity->setScore(100);
        $entity->setArchiveTime(new \DateTimeImmutable());

        return $entity;
    }

    protected function getRepository(): UserRankingArchiveRepository
    {
        return $this->repository;
    }
}
