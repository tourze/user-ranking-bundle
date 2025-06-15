<?php

namespace UserRankingBundle\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;
use UserRankingBundle\UserRankingBundle;

class UserRankingBlacklistRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRankingBlacklistRepository $repository;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            UserRankingBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->repository = $container->get(UserRankingBlacklistRepository::class);

        // 创建Schema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testFindByUserIdAndList(): void
    {
        // 创建一个排行榜
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');
        $list->setValid(true);
        $this->entityManager->persist($list);
        $this->entityManager->flush();

        // 创建测试用户黑名单
        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('user123');
        $blacklist->setList($list);
        $blacklist->setValid(true);
        $blacklist->setComment('测试黑名单');
        $this->entityManager->persist($blacklist);
        $this->entityManager->flush();

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
        $this->entityManager->persist($list);

        // 创建已过期的黑名单
        $expiredBlacklist = new UserRankingBlacklist();
        $expiredBlacklist->setUserId('expired_user');
        $expiredBlacklist->setList($list);
        $expiredBlacklist->setValid(true);
        $expiredBlacklist->setComment('已过期');
        $expiredBlacklist->setExpireTime(new \DateTimeImmutable('-1 day'));
        $this->entityManager->persist($expiredBlacklist);

        // 创建未过期的黑名单
        $activeBlacklist = new UserRankingBlacklist();
        $activeBlacklist->setUserId('active_user');
        $activeBlacklist->setList($list);
        $activeBlacklist->setValid(true);
        $activeBlacklist->setComment('未过期');
        $activeBlacklist->setExpireTime(new \DateTimeImmutable('+1 day'));
        $this->entityManager->persist($activeBlacklist);

        // 创建无过期时间的黑名单
        $noExpireBlacklist = new UserRankingBlacklist();
        $noExpireBlacklist->setUserId('forever_user');
        $noExpireBlacklist->setList($list);
        $noExpireBlacklist->setValid(true);
        $noExpireBlacklist->setComment('永久黑名单');
        $this->entityManager->persist($noExpireBlacklist);

        $this->entityManager->flush();

        // 清理过期黑名单
        $removedCount = $this->repository->removeExpired();
        $this->assertEquals(1, $removedCount);

        // 验证只删除了过期的黑名单
        $this->assertNull($this->repository->findByUserIdAndList('expired_user', $list));
        $this->assertNotNull($this->repository->findByUserIdAndList('active_user', $list));
        $this->assertNotNull($this->repository->findByUserIdAndList('forever_user', $list));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // 清理以避免内存泄漏
        $em = $this->entityManager;
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        if ($em) {
            $em->close();
        }
    }
} 