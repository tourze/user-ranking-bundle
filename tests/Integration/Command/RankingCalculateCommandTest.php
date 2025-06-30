<?php

namespace UserRankingBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use UserRankingBundle\Command\RankingCalculateCommand;
use UserRankingBundle\Entity\UserRankingList;

class RankingCalculateCommandTest extends IntegrationTestCase
{
    private EntityManagerInterface $entityManager;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        
        $application = new Application(self::$kernel);
        $command = $application->find(RankingCalculateCommand::NAME);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNonExistentList(): void
    {
        $this->commandTester->execute([
            'list-id' => 999999,
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('排行榜 999999 不存在', $this->commandTester->getDisplay());
    }

    public function testExecuteWithoutValidLists(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertTrue(
            str_contains($output, '没有找到需要计算的排行榜') ||
            str_contains($output, '[OK]')
        );
    }

    public function testExecuteDryRun(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setValid(true)
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $this->commandTester->execute([
            'list-id' => $list->getId(),
            'dry-run' => '1',
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithListWithoutScoreSql(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setValid(true)
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $this->commandTester->execute([
            'list-id' => $list->getId(),
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('未配置计算SQL，跳过', $this->commandTester->getDisplay());
    }
}