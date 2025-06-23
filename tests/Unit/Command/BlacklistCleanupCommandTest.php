<?php

namespace UserRankingBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use UserRankingBundle\Command\BlacklistCleanupCommand;

/**
 * BlacklistCleanupCommand 命令测试
 */
class BlacklistCleanupCommandTest extends TestCase
{
    public function testCommandIsRegistered(): void
    {
        // 创建应用程序和命令
        $application = new Application();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $command = new BlacklistCleanupCommand($entityManager);
        
        $application->add($command);
        
        // 验证命令已注册
        $this->assertTrue($application->has('user-ranking:blacklist-cleanup'));
        $this->assertInstanceOf(BlacklistCleanupCommand::class, $application->find('user-ranking:blacklist-cleanup'));
    }
} 