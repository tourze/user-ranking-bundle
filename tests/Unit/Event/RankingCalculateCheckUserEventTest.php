<?php

namespace UserRankingBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Event\RankingCalculateCheckUserEvent;

class RankingCalculateCheckUserEventTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $event = new RankingCalculateCheckUserEvent();
        
        // 测试 userId (int)
        $event->setUserId(123);
        $this->assertEquals(123, $event->getUserId());
        
        // 测试 userId (string)
        $event->setUserId('user123');
        $this->assertEquals('user123', $event->getUserId());
        
        // 测试 isBlacklist
        $this->assertFalse($event->isBlacklist()); // 默认为 false
        
        $event->setIsBlacklist(true);
        $this->assertTrue($event->isBlacklist());
        
        $event->setIsBlacklist(false);
        $this->assertFalse($event->isBlacklist());
    }

    public function testDefaultValues(): void
    {
        $event = new RankingCalculateCheckUserEvent();
        
        // isBlacklist 默认应为 false
        $this->assertFalse($event->isBlacklist());
    }
}