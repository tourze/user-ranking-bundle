<?php

namespace UserRankingBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;

/**
 * @internal
 */
#[CoversClass(UserRankingBlacklistRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingBlacklistRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRankingBlacklistRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getService(UserRankingBlacklistRepository::class);
        $this->assertInstanceOf(UserRankingBlacklistRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testFindByUserIdAndList(): void
    {
        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager = self::getEntityManager();
        $entityManager->persist($list);
        $entityManager->flush();

        // 创建测试用户黑名单
        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('user123');
        $blacklist->setList($list);
        $blacklist->setValid(true);
        $blacklist->setComment('测试黑名单');
        $entityManager->persist($blacklist);
        $entityManager->flush();

        // 使用存储库查找
        $result = $this->repository->findByUserIdAndList('user123', $list);
        $this->assertNotNull($result);
        $this->assertEquals('user123', $result->getUserId());
        $this->assertSame($list, $result->getList());

        // 测试找不到的情况
        $result = $this->repository->findByUserIdAndList('nonexistent', $list);
        $this->assertNull($result);
    }

    public function testRemoveExpired(): void
    {
        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager = self::getEntityManager();
        $entityManager->persist($list);

        // 创建已过期的黑名单
        $expiredBlacklist = new UserRankingBlacklist();
        $expiredBlacklist->setUserId('expired_user');
        $expiredBlacklist->setList($list);
        $expiredBlacklist->setValid(true);
        $expiredBlacklist->setComment('已过期');
        $expiredBlacklist->setExpireTime(new \DateTimeImmutable('-1 day'));
        $entityManager->persist($expiredBlacklist);

        // 创建未过期的黑名单
        $activeBlacklist = new UserRankingBlacklist();
        $activeBlacklist->setUserId('active_user');
        $activeBlacklist->setList($list);
        $activeBlacklist->setValid(true);
        $activeBlacklist->setComment('未过期');
        $activeBlacklist->setExpireTime(new \DateTimeImmutable('+1 day'));
        $entityManager->persist($activeBlacklist);

        // 创建无过期时间的黑名单
        $noExpireBlacklist = new UserRankingBlacklist();
        $noExpireBlacklist->setUserId('forever_user');
        $noExpireBlacklist->setList($list);
        $noExpireBlacklist->setValid(true);
        $noExpireBlacklist->setComment('永久黑名单');
        $entityManager->persist($noExpireBlacklist);

        $entityManager->flush();

        // 清理过期黑名单
        $removedCount = $this->repository->removeExpired();
        $this->assertEquals(1, $removedCount);

        // 验证只删除了过期的黑名单
        $this->assertNull($this->repository->findByUserIdAndList('expired_user', $list));
        $this->assertNotNull($this->repository->findByUserIdAndList('active_user', $list));
        $this->assertNotNull($this->repository->findByUserIdAndList('forever_user', $list));
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager->persist($list);
        $entityManager->flush();

        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('saved_user');
        $blacklist->setList($list);
        $blacklist->setValid(true);
        $blacklist->setComment('保存的黑名单');

        $this->repository->save($blacklist);

        // 验证实体已保存
        $this->assertNotNull($blacklist->getId());

        $foundBlacklist = $this->repository->find($blacklist->getId());
        $this->assertInstanceOf(UserRankingBlacklist::class, $foundBlacklist);
        $this->assertEquals('saved_user', $foundBlacklist->getUserId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entityManager = self::getEntityManager();

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager->persist($list);

        // 创建并保存实体
        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('to_be_removed');
        $blacklist->setList($list);
        $blacklist->setValid(true);
        $blacklist->setComment('待删除的黑名单');

        $entityManager->persist($blacklist);
        $entityManager->flush();

        $blacklistId = $blacklist->getId();
        $this->assertNotNull($blacklistId);

        // 删除实体
        $this->repository->remove($blacklist);

        // 验证实体已删除
        $foundBlacklist = $this->repository->find($blacklistId);
        $this->assertNull($foundBlacklist);
    }

    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingBlacklist')->execute();

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager->persist($list);

        // 创建多个测试实体
        $blacklist1 = new UserRankingBlacklist();
        $blacklist1->setUserId('aaa_user');
        $blacklist1->setList($list);
        $blacklist1->setValid(true);
        $blacklist1->setComment('AAA用户');

        $blacklist2 = new UserRankingBlacklist();
        $blacklist2->setUserId('zzz_user');
        $blacklist2->setList($list);
        $blacklist2->setValid(true);
        $blacklist2->setComment('ZZZ用户');

        $entityManager->persist($blacklist1);
        $entityManager->persist($blacklist2);
        $entityManager->flush();

        // 按 userId 升序查找第一个
        $foundBlacklist = $this->repository->findOneBy(['valid' => true], ['userId' => 'ASC']);

        $this->assertInstanceOf(UserRankingBlacklist::class, $foundBlacklist);
        $this->assertEquals('aaa_user', $foundBlacklist->getUserId());

        // 按 userId 降序查找第一个
        $foundBlacklist = $this->repository->findOneBy(['valid' => true], ['userId' => 'DESC']);

        $this->assertInstanceOf(UserRankingBlacklist::class, $foundBlacklist);
        $this->assertEquals('zzz_user', $foundBlacklist->getUserId());
    }

    public function testFindOneByWithNullCriteriaShouldReturnEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $entityManager->persist($list);

        // 创建一个 expireTime 为 null 的实体
        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('null_expire_user');
        $blacklist->setList($list);
        $blacklist->setValid(true);
        $blacklist->setComment('无过期时间');
        $blacklist->setExpireTime(null);

        $entityManager->persist($blacklist);
        $entityManager->flush();

        $foundBlacklist = $this->repository->findOneBy(['expireTime' => null]);

        $this->assertInstanceOf(UserRankingBlacklist::class, $foundBlacklist);
        $this->assertNull($foundBlacklist->getExpireTime());
    }

    public function testCountByAssociationListShouldReturnCorrectNumber(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingBlacklist')->execute();

        // 创建两个排行榜
        $list1 = new UserRankingList();
        $list1->setTitle('排行槜1');
        $list1->setValid(true);
        $entityManager->persist($list1);

        $list2 = new UserRankingList();
        $list2->setTitle('排行槜2');
        $list2->setValid(true);
        $entityManager->persist($list2);

        // 为第一个排行榜创建3个黑名单
        for ($i = 1; $i <= 3; ++$i) {
            $blacklist = new UserRankingBlacklist();
            $blacklist->setUserId('list1_user' . $i);
            $blacklist->setList($list1);
            $blacklist->setValid(true);
            $blacklist->setComment('列表1用户' . $i);
            $entityManager->persist($blacklist);
        }

        // 为第二个排行榜创建2个黑名单
        for ($i = 1; $i <= 2; ++$i) {
            $blacklist = new UserRankingBlacklist();
            $blacklist->setUserId('list2_user' . $i);
            $blacklist->setList($list2);
            $blacklist->setValid(true);
            $blacklist->setComment('列表2用户' . $i);
            $entityManager->persist($blacklist);
        }
        $entityManager->flush();

        $count = $this->repository->count(['list' => $list1]);
        $this->assertSame(3, $count);
    }

    public function testFindOneByAssociationListShouldReturnMatchingEntity(): void
    {
        $entityManager = self::getEntityManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        // 清空表
        $entityManager->createQuery('DELETE FROM UserRankingBundle\Entity\UserRankingBlacklist')->execute();

        // 创建两个排行榜
        $list1 = new UserRankingList();
        $list1->setTitle('排行榜1');
        $list1->setValid(true);
        $entityManager->persist($list1);

        $list2 = new UserRankingList();
        $list2->setTitle('排行榜2');
        $list2->setValid(true);
        $entityManager->persist($list2);

        // 为排行榜创建黑名单
        $blacklist1 = new UserRankingBlacklist();
        $blacklist1->setUserId('list1_user');
        $blacklist1->setList($list1);
        $blacklist1->setValid(true);
        $blacklist1->setComment('列表1用户');

        $blacklist2 = new UserRankingBlacklist();
        $blacklist2->setUserId('list2_user');
        $blacklist2->setList($list2);
        $blacklist2->setValid(true);
        $blacklist2->setComment('列表2用户');

        $entityManager->persist($blacklist1);
        $entityManager->persist($blacklist2);
        $entityManager->flush();

        $foundBlacklist = $this->repository->findOneBy(['list' => $list1]);

        $this->assertInstanceOf(UserRankingBlacklist::class, $foundBlacklist);
        $this->assertSame($list1, $foundBlacklist->getList());
        $this->assertEquals('list1_user', $foundBlacklist->getUserId());
    }

    /**
     * @return UserRankingBlacklist
     */
    protected function createNewEntity(): object
    {
        $list = new UserRankingList();
        $list->setTitle('Test List for Blacklist');
        $list->setValid(true);

        $entity = new UserRankingBlacklist();
        $entity->setUserId('test_user_' . uniqid());
        $entity->setList($list);
        $entity->setValid(true);
        $entity->setReason('Test blacklist reason');
        $entity->setExpireTime(new \DateTimeImmutable('+1 day'));

        return $entity;
    }

    protected function getRepository(): UserRankingBlacklistRepository
    {
        return $this->repository;
    }
}
