<?php

namespace UserRankingBundle\Tests\Integration\Repository;

use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingListRepository;

class UserRankingListRepositoryTest extends IntegrationTestCase
{
    private UserRankingListRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->repository = self::getContainer()->get(UserRankingListRepository::class);
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingListRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistList(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setValid(true)
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);
               
        $entityManager->persist($list);
        $entityManager->flush();
        
        // 验证保存成功
        $found = $this->repository->find($list->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Test List', $found->getTitle());
        $this->assertTrue($found->isValid());
    }

    public function testFindValidLists(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建有效的排行榜
        $validList = new UserRankingList();
        $validList->setTitle('Valid List')
                 ->setValid(true)
                 ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);
        
        // 创建无效的排行榜
        $invalidList = new UserRankingList();
        $invalidList->setTitle('Invalid List')
                   ->setValid(false)
                   ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);
        
        $entityManager->persist($validList);
        $entityManager->persist($invalidList);
        $entityManager->flush();
        
        // 查找有效的排行榜
        $validLists = $this->repository->findBy(['valid' => true]);
        $this->assertNotEmpty($validLists);
        
        foreach ($validLists as $list) {
            $this->assertTrue($list->isValid());
        }
    }
}