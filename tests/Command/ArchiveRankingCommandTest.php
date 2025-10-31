<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use UserRankingBundle\Command\ArchiveRankingCommand;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(ArchiveRankingCommand::class)]
final class ArchiveRankingCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getService(ArchiveRankingCommand::class);
        $this->assertInstanceOf(ArchiveRankingCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandIsRegistered(): void
    {
        $command = self::getService(ArchiveRankingCommand::class);
        $this->assertInstanceOf(Command::class, $command);
        $this->assertSame(ArchiveRankingCommand::NAME, $command->getName());
    }

    public function testExecuteWithNonExistentList(): void
    {
        $this->commandTester->execute(['list-id' => 999]);
        $this->assertSame(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('排行榜 999 不存在', $this->commandTester->getDisplay());
    }

    public function testExecuteWithEmptyRankingData(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test Ranking');
        $list->setValid(true);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        $this->commandTester->execute(['list-id' => $list->getId()]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('当前排行榜没有排名数据', $this->commandTester->getDisplay());
    }

    public function testArgumentListId(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test Ranking');
        $list->setValid(true);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        // Test with valid list-id
        $this->commandTester->execute(['list-id' => $list->getId()]);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        // Test with invalid list-id
        $this->commandTester->execute(['list-id' => 999]);
        $this->assertSame(1, $this->commandTester->getStatusCode());
    }

    public function testOptionKeepDays(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test Ranking');
        $list->setValid(true);

        $em = self::getEntityManager();
        $em->persist($list);
        $em->flush();

        // Test with default keep-days (30)
        $this->commandTester->execute(['list-id' => $list->getId()]);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        // Test with custom keep-days
        $this->commandTester->execute([
            'list-id' => $list->getId(),
            '--keep-days' => '7',
        ]);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        // Test with short form
        $this->commandTester->execute([
            'list-id' => $list->getId(),
            '-k' => '14',
        ]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }
}
