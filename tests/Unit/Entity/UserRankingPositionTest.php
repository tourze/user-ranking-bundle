<?php

namespace UserRankingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Entity\UserRankingPosition;

class UserRankingPositionTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $position = new UserRankingPosition();
        
        $position->setValid(true);
        $this->assertTrue($position->isValid());
        
        $position->setTitle('Test Position');
        $this->assertEquals('Test Position', $position->getTitle());
    }

    public function testToString(): void
    {
        $position = new UserRankingPosition();
        
        // ID 为 null 时应返回空字符串
        $this->assertEquals('', $position->__toString());
        
        $position->setTitle('Test Position');
        // 由于 ID 仍为 null，仍返回空字符串
        $this->assertEquals('', $position->__toString());
    }

    public function testToStringWithTitle(): void
    {
        $position = new UserRankingPosition();
        $position->setTitle('Test Position');
        
        // 手动设置 ID 来测试 __toString
        $reflection = new \ReflectionClass($position);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($position, '123');
        
        $this->assertEquals('Test Position', $position->__toString());
    }

    public function testListsCollection(): void
    {
        $position = new UserRankingPosition();
        $list = new UserRankingList();
        
        // 初始应为空
        $this->assertCount(0, $position->getLists());
        
        // 添加 list
        $position->addList($list);
        $this->assertCount(1, $position->getLists());
        $this->assertTrue($position->getLists()->contains($list));
        
        // 重复添加不应增加数量
        $position->addList($list);
        $this->assertCount(1, $position->getLists());
        
        // 移除 list
        $position->removeList($list);
        $this->assertCount(0, $position->getLists());
        $this->assertFalse($position->getLists()->contains($list));
    }

    public function testFluentInterface(): void
    {
        $position = new UserRankingPosition();
        $list = new UserRankingList();
        
        $result = $position->setValid(true)
                          ->setTitle('Test Position')
                          ->addList($list);
        
        $this->assertSame($position, $result);
    }
}