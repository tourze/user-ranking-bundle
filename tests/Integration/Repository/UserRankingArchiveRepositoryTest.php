<?php

namespace UserRankingBundle\Tests\Integration\Repository;

use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingArchiveRepository;

class UserRankingArchiveRepositoryTest extends IntegrationTestCase
{
    private UserRankingArchiveRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->repository = self::getContainer()->get(UserRankingArchiveRepository::class);
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingArchiveRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertNotNull($result);
    }

    public function testCreateAndPersistArchive(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);
            
        $entityManager->persist($list);
        
        $archive = new UserRankingArchive();
        $archive->setList($list)
               ->setNumber(1)
               ->setUserId('123')
               ->setScore(100)
               ->setArchiveTime(new \DateTimeImmutable());
               
        $entityManager->persist($archive);
        $entityManager->flush();
        
        // 验证保存成功
        $found = $this->repository->find($archive->getId());
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->getNumber());
        $this->assertEquals('123', $found->getUserId());
        $this->assertEquals(100, $found->getScore());
    }
}