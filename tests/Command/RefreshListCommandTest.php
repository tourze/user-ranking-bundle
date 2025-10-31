<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use UserRankingBundle\Command\RefreshListCommand;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(RefreshListCommand::class)]
final class RefreshListCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getService(RefreshListCommand::class);
        $this->assertInstanceOf(RefreshListCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandIsRegistered(): void
    {
        $command = self::getService(RefreshListCommand::class);
        $this->assertInstanceOf(Command::class, $command);
        $this->assertSame(RefreshListCommand::NAME, $command->getName());
    }

    public function testExecuteWithNoValidLists(): void
    {
        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidLists(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Invalid Ranking');
        $list->setValid(false);
        $list->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithListsWithoutRefreshFrequency(): void
    {
        $list = new UserRankingList();
        $list->setTitle('No Frequency Ranking');
        $list->setValid(true);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithListsNeverRefreshed(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Never Refreshed Ranking');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithDailyRefreshFrequency(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Daily Ranking');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $list->setRefreshTime(new \DateTimeImmutable('-2 days'));

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithRecentlyRefreshedDailyList(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Recently Refreshed Daily Ranking');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::DAILY);
        $list->setRefreshTime(new \DateTimeImmutable());

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithHighFrequencyRefresh(): void
    {
        $list = new UserRankingList();
        $list->setTitle('High Frequency Ranking');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);
        $list->setRefreshTime(new \DateTimeImmutable('-2 minutes'));

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithRecentlyRefreshedHighFrequencyList(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Recently Refreshed High Frequency Ranking');
        $list->setValid(true);
        $list->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);
        $list->setRefreshTime(new \DateTimeImmutable('-10 seconds'));

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithMixedLists(): void
    {
        $needsRefresh = new UserRankingList();
        $needsRefresh->setTitle('Needs Refresh Ranking');
        $needsRefresh->setValid(true);
        $needsRefresh->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);
        $needsRefresh->setRefreshTime(new \DateTimeImmutable('-2 minutes'));

        $recentlyRefreshed = new UserRankingList();
        $recentlyRefreshed->setTitle('Recently Refreshed Ranking');
        $recentlyRefreshed->setValid(true);
        $recentlyRefreshed->setRefreshFrequency(RefreshFrequency::EVERY_MINUTE);
        $recentlyRefreshed->setRefreshTime(new \DateTimeImmutable('-10 seconds'));

        $noFrequency = new UserRankingList();
        $noFrequency->setTitle('No Frequency Ranking');
        $noFrequency->setValid(true);

        $em = self::getEntityManager();
        $em->persist($needsRefresh);
        $em->persist($recentlyRefreshed);
        $em->persist($noFrequency);
        $em->flush();

        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }
}
