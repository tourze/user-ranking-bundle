<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use UserRankingBundle\Command\BlacklistCleanupCommand;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(BlacklistCleanupCommand::class)]
final class BlacklistCleanupCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getService(BlacklistCleanupCommand::class);
        $this->assertInstanceOf(BlacklistCleanupCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandIsRegistered(): void
    {
        $command = self::getService(BlacklistCleanupCommand::class);
        $this->assertInstanceOf(Command::class, $command);
        $this->assertSame(BlacklistCleanupCommand::NAME, $command->getName());
    }

    public function testExecuteWithNoExpiredBlacklists(): void
    {
        $this->commandTester->execute([]);
        $this->assertSame(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('没有需要清理的黑名单记录', $this->commandTester->getDisplay());
    }
}
