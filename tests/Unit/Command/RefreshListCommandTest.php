<?php

namespace UserRankingBundle\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use UserRankingBundle\Command\RefreshListCommand;
use UserRankingBundle\Repository\UserRankingListRepository;

class RefreshListCommandTest extends TestCase
{
    public function testCommandIsRegistered(): void
    {
        // 创建应用程序和命令
        $application = new Application();
        $listRepository = $this->createMock(UserRankingListRepository::class);
        $messageBus = $this->createMock(\Symfony\Component\Messenger\MessageBusInterface::class);
        $command = new RefreshListCommand($listRepository, $messageBus);
        
        $application->add($command);
        
        // 验证命令已注册
        $this->assertTrue($application->has('user-ranking:refresh-list'));
        $this->assertInstanceOf(RefreshListCommand::class, $application->find('user-ranking:refresh-list'));
    }
} 