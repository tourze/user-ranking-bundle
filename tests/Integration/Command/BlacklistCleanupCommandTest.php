<?php

namespace UserRankingBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use UserRankingBundle\Command\BlacklistCleanupCommand;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

class BlacklistCleanupCommandTest extends IntegrationTestCase
{
    private EntityManagerInterface $entityManager;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        
        $application = new Application(self::$kernel);
        $command = $application->find(BlacklistCleanupCommand::NAME);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoExpiredBlacklist(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('没有需要清理的黑名单记录', $this->commandTester->getDisplay());
    }

    public function testExecuteWithExpiredBlacklist(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $this->entityManager->persist($list);

        // 创建一个已过期的黑名单记录（使用 unblockTime）
        $blacklist = new UserRankingBlacklist();
        $blacklist->setList($list)
            ->setUserId('123')
            ->setReason('Test reason')
            ->setValid(true)
            ->setUnblockTime(new \DateTimeImmutable('-1 day'));

        $this->entityManager->persist($blacklist);
        $this->entityManager->flush();

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('成功清理 1 条已过期的黑名单记录', $this->commandTester->getDisplay());

        // 刷新实体管理器以获取最新状态
        $this->entityManager->clear();
        
        // 验证黑名单记录被标记为无效
        $blacklistRepository = self::getContainer()->get('UserRankingBundle\\Repository\\UserRankingBlacklistRepository');
        $updatedBlacklist = $blacklistRepository->find($blacklist->getId());
        $this->assertNotNull($updatedBlacklist);
        $this->assertFalse($updatedBlacklist->isValid());
    }
}