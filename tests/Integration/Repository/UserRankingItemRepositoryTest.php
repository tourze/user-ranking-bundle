<?php

namespace UserRankingBundle\Tests\Integration\Repository;

use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingItemRepository;

class UserRankingItemRepositoryTest extends IntegrationTestCase
{
    private UserRankingItemRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->repository = self::getContainer()->get(UserRankingItemRepository::class);
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingItemRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistItem(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建测试数据
        $list = new UserRankingList();
        $list->setTitle('Test List')
            ->setRefreshFrequency(\UserRankingBundle\Enum\RefreshFrequency::DAILY);
            
        $entityManager->persist($list);
        
        $item = new UserRankingItem();
        $item->setList($list)
            ->setNumber(1)
            ->setUserId('123')
            ->setScore(100)
            ->setValid(true)
            ->setFixed(false);
               
        $entityManager->persist($item);
        $entityManager->flush();
        
        // 验证保存成功
        $found = $this->repository->find($item->getId());
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->getNumber());
        $this->assertEquals('123', $found->getUserId());
        $this->assertEquals(100, $found->getScore());
        $this->assertTrue($found->isValid());
        $this->assertFalse($found->isFixed());
    }
}