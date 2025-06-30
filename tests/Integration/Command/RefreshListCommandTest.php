<?php

namespace UserRankingBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use UserRankingBundle\Command\RefreshListCommand;
use UserRankingBundle\Entity\UserRankingList;

class RefreshListCommandTest extends IntegrationTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $application = new Application(self::$kernel);
        $command = $application->find(RefreshListCommand::NAME);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoValidLists(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithValidLists(): void
    {
        // 创建一个有效的排行榜
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setValid(true)
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($list);
        $entityManager->flush();

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}