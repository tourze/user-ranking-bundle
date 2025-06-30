<?php

namespace UserRankingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;

class UserRankingItemTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $item = new UserRankingItem();
        
        $item->setValid(true);
        $this->assertTrue($item->isValid());
        
        $list = new UserRankingList();
        $list->setTitle('Test List');
        $item->setList($list);
        $this->assertSame($list, $item->getList());
        
        $item->setNumber(1);
        $this->assertEquals(1, $item->getNumber());
        
        $item->setUserId('123');
        $this->assertEquals('123', $item->getUserId());
        
        $item->setTextReason('Test reason');
        $this->assertEquals('Test reason', $item->getTextReason());
        
        $item->setScore(100);
        $this->assertEquals(100, $item->getScore());
        
        $item->setFixed(true);
        $this->assertTrue($item->isFixed());
        
        $item->setRecommendThumb('thumb.jpg');
        $this->assertEquals('thumb.jpg', $item->getRecommendThumb());
        
        $item->setRecommendReason('Recommend reason');
        $this->assertEquals('Recommend reason', $item->getRecommendReason());
    }

    public function testToString(): void
    {
        $item = new UserRankingItem();
        $list = new UserRankingList();
        $list->setTitle('Test List');
        $item->setList($list);
        $item->setNumber(1);
        
        $this->assertEquals('Test List - 1', $item->__toString());
    }

    public function testToStringWithNullList(): void
    {
        $item = new UserRankingItem();
        $item->setNumber(1);
        
        $this->assertEquals(' - 1', $item->__toString());
    }

    public function testFluentInterface(): void
    {
        $item = new UserRankingItem();
        $list = new UserRankingList();
        
        $result = $item->setValid(true)
                      ->setList($list)
                      ->setNumber(1)
                      ->setUserId('123')
                      ->setTextReason('Test reason')
                      ->setScore(100)
                      ->setFixed(true)
                      ->setRecommendThumb('thumb.jpg')
                      ->setRecommendReason('Recommend reason');
        
        $this->assertSame($item, $result);
    }
}