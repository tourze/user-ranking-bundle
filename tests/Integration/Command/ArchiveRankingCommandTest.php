<?php

namespace UserRankingBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use UserRankingBundle\Command\ArchiveRankingCommand;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingArchiveRepository;

class ArchiveRankingCommandTest extends IntegrationTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRankingArchiveRepository $archiveRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->archiveRepository = self::getContainer()->get(UserRankingArchiveRepository::class);
        
        $application = new Application(self::$kernel);
        $command = $application->find(ArchiveRankingCommand::NAME);
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

    public function testExecuteWithEmptyRanking(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $this->commandTester->execute([
            'list-id' => $list->getId(),
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('当前排行榜没有排名数据', $this->commandTester->getDisplay());
    }

    public function testExecuteWithRankingData(): void
    {
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);

        $this->entityManager->persist($list);

        $item = new UserRankingItem();
        $item->setList($list)
            ->setNumber(1)
            ->setUserId('123')
            ->setScore(100);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->commandTester->execute([
            'list-id' => $list->getId(),
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('成功归档 1 条排名数据', $this->commandTester->getDisplay());

        // 验证归档数据
        $archives = $this->archiveRepository->findBy(['list' => $list]);
        $this->assertCount(1, $archives);
        
        $archive = $archives[0];
        $this->assertEquals(1, $archive->getNumber());
        $this->assertEquals('123', $archive->getUserId());
        $this->assertEquals(100, $archive->getScore());
    }
}