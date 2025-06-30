<?php

namespace UserRankingBundle\Tests\Integration\Repository;

use UserRankingBundle\Tests\Integration\IntegrationTestCase;
use UserRankingBundle\Entity\UserRankingPosition;
use UserRankingBundle\Repository\UserRankingPositionRepository;

class UserRankingPositionRepositoryTest extends IntegrationTestCase
{
    private UserRankingPositionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->repository = self::getContainer()->get(UserRankingPositionRepository::class);
    }

    public function testRepository(): void
    {
        $this->assertInstanceOf(UserRankingPositionRepository::class, $this->repository);
    }

    public function testFindMethod(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testCreateAndPersistPosition(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建测试数据
        $position = new UserRankingPosition();
        $position->setTitle('Test Position')
                ->setValid(true);
               
        $entityManager->persist($position);
        $entityManager->flush();
        
        // 验证保存成功
        $found = $this->repository->find($position->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Test Position', $found->getTitle());
        $this->assertTrue($found->isValid());
    }

    public function testFindValidPositions(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // 创建有效的位置
        $validPosition = new UserRankingPosition();
        $validPosition->setTitle('Valid Position')
                     ->setValid(true);
        
        // 创建无效的位置
        $invalidPosition = new UserRankingPosition();
        $invalidPosition->setTitle('Invalid Position')
                       ->setValid(false);
        
        $entityManager->persist($validPosition);
        $entityManager->persist($invalidPosition);
        $entityManager->flush();
        
        // 查找有效的位置
        $validPositions = $this->repository->findBy(['valid' => true]);
        $this->assertNotEmpty($validPositions);
        
        foreach ($validPositions as $position) {
            $this->assertTrue($position->isValid());
        }
    }
}