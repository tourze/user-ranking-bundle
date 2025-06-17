<?php

namespace UserRankingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Entity\UserRankingPosition;
use UserRankingBundle\Enum\RefreshFrequency;

class UserRankingListTest extends TestCase
{
    private UserRankingList $userRankingList;

    protected function setUp(): void
    {
        $this->userRankingList = new UserRankingList();
    }

    public function testGetterAndSetterForTitle(): void
    {
        $title = '测试排行榜';
        $this->userRankingList->setTitle($title);
        $this->assertEquals($title, $this->userRankingList->getTitle());
    }

    public function testGetterAndSetterForSubtitle(): void
    {
        $subtitle = '测试副标题';
        $this->userRankingList->setSubtitle($subtitle);
        $this->assertEquals($subtitle, $this->userRankingList->getSubtitle());
    }

    public function testGetterAndSetterForColor(): void
    {
        $color = '#FF5733';
        $this->userRankingList->setColor($color);
        $this->assertEquals($color, $this->userRankingList->getColor());
    }

    public function testGetterAndSetterForLogoUrl(): void
    {
        $logoUrl = 'https://example.com/logo.png';
        $this->userRankingList->setLogoUrl($logoUrl);
        $this->assertEquals($logoUrl, $this->userRankingList->getLogoUrl());
    }

    public function testGetterAndSetterForCount(): void
    {
        $count = 10;
        $this->userRankingList->setCount($count);
        $this->assertEquals($count, $this->userRankingList->getCount());
    }

    public function testGetterAndSetterForScoreSql(): void
    {
        $scoreSql = 'SELECT id, score FROM items ORDER BY score DESC';
        $this->userRankingList->setScoreSql($scoreSql);
        $this->assertEquals($scoreSql, $this->userRankingList->getScoreSql());
    }

    public function testGetterAndSetterForValid(): void
    {
        // 默认值应该是 false
        $this->assertFalse($this->userRankingList->isValid());

        // 设置为 true
        $this->userRankingList->setValid(true);
        $this->assertTrue($this->userRankingList->isValid());

        // 设置为 false
        $this->userRankingList->setValid(false);
        $this->assertFalse($this->userRankingList->isValid());

        // 设置为 null
        $this->userRankingList->setValid(null);
        $this->assertNull($this->userRankingList->isValid());
    }

    public function testGetterAndSetterForRefreshFrequency(): void
    {
        // 默认值应该是 EVERY_MINUTE
        $this->assertEquals(RefreshFrequency::EVERY_MINUTE, $this->userRankingList->getRefreshFrequency());

        // 设置为 DAILY
        $this->userRankingList->setRefreshFrequency(RefreshFrequency::DAILY);
        $this->assertEquals(RefreshFrequency::DAILY, $this->userRankingList->getRefreshFrequency());

        // 设置为 null
        $this->userRankingList->setRefreshFrequency(null);
        $this->assertNull($this->userRankingList->getRefreshFrequency());
    }

    public function testAddAndRemoveItem(): void
    {
        $item = $this->createMock(UserRankingItem::class);
        $item->expects($this->once())
            ->method('setList')
            ->with($this->userRankingList);

        $this->userRankingList->addItem($item);
        $this->assertTrue($this->userRankingList->getItems()->contains($item));

        $this->userRankingList->removeItem($item);
        $this->assertFalse($this->userRankingList->getItems()->contains($item));
    }

    public function testAddAndRemovePosition(): void
    {
        $position = $this->createMock(UserRankingPosition::class);
        
        $this->userRankingList->addPosition($position);
        $this->assertTrue($this->userRankingList->getPositions()->contains($position));

        $this->userRankingList->removePosition($position);
        $this->assertFalse($this->userRankingList->getPositions()->contains($position));
    }

    public function testGetterAndSetterForCreatedBy(): void
    {
        $createdBy = 'test_user';
        $this->userRankingList->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->userRankingList->getCreatedBy());
    }

    public function testGetterAndSetterForUpdatedBy(): void
    {
        $updatedBy = 'test_admin';
        $this->userRankingList->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->userRankingList->getUpdatedBy());
    }

    public function testUpdateRefreshTime(): void
    {
        $this->assertNull($this->userRankingList->getRefreshTime());
        
        $this->userRankingList->updateRefreshTime();
        $refreshTime = $this->userRankingList->getRefreshTime();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $refreshTime);
        $this->assertEqualsWithDelta(time(), $refreshTime->getTimestamp(), 2);
    }

    public function testRetrieveLockResource(): void
    {
        // 由于 ID 是由 Doctrine 生成的，我们需要模拟这个行为
        $reflectionClass = new \ReflectionClass(UserRankingList::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->userRankingList, '12345');

        $expectedLockResource = 'user_ranking_list_12345';
        $this->assertEquals($expectedLockResource, $this->userRankingList->retrieveLockResource());
    }

    public function testToString(): void
    {
        // 当 ID 为 null 时应该返回空字符串
        $this->assertEquals('', $this->userRankingList->__toString());

        // 设置标题和 ID
        $this->userRankingList->setTitle('测试排行榜');
        $reflectionClass = new \ReflectionClass(UserRankingList::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->userRankingList, '12345');

        $this->assertEquals('测试排行榜', $this->userRankingList->__toString());
    }

    public function testIsInValidPeriod_WithNoTimeConstraints(): void
    {
        // 没有设置开始和结束时间，应该返回 true
        $now = new \DateTimeImmutable();
        $this->assertTrue($this->userRankingList->isInValidPeriod($now));
    }

    public function testIsInValidPeriod_WithStartTimeOnly(): void
    {
        $now = new \DateTimeImmutable();
        
        // 设置过去的开始时间
        $startTime = $now->modify('-1 day');
        $this->userRankingList->setStartTime($startTime);
        
        // 当前时间在开始时间之后，应该返回 true
        $this->assertTrue($this->userRankingList->isInValidPeriod($now));
        
        // 设置未来的开始时间
        $startTime = $now->modify('+1 day');
        $this->userRankingList->setStartTime($startTime);
        
        // 当前时间在开始时间之前，应该返回 false
        $this->assertFalse($this->userRankingList->isInValidPeriod($now));
    }

    public function testIsInValidPeriod_WithEndTimeOnly(): void
    {
        $now = new \DateTimeImmutable();
        
        // 设置未来的结束时间
        $endTime = $now->modify('+1 day');
        $this->userRankingList->setEndTime($endTime);
        
        // 当前时间在结束时间之前，应该返回 true
        $this->assertTrue($this->userRankingList->isInValidPeriod($now));
        
        // 设置过去的结束时间
        $endTime = $now->modify('-1 day');
        $this->userRankingList->setEndTime($endTime);
        
        // 当前时间在结束时间之后，应该返回 false
        $this->assertFalse($this->userRankingList->isInValidPeriod($now));
    }

    public function testIsInValidPeriod_WithStartAndEndTime(): void
    {
        $now = new \DateTimeImmutable();
        
        // 设置过去的开始时间和未来的结束时间
        $startTime = $now->modify('-1 day');
        $endTime = $now->modify('+1 day');
        
        $this->userRankingList->setStartTime($startTime);
        $this->userRankingList->setEndTime($endTime);
        
        // 当前时间在开始和结束时间之间，应该返回 true
        $this->assertTrue($this->userRankingList->isInValidPeriod($now));
        
        // 设置未来的开始时间和结束时间
        $startTime = $now->modify('+1 day');
        $endTime = $now->modify('+2 days');
        
        $this->userRankingList->setStartTime($startTime);
        $this->userRankingList->setEndTime($endTime);
        
        // 当前时间在开始时间之前，应该返回 false
        $this->assertFalse($this->userRankingList->isInValidPeriod($now));
        
        // 设置过去的开始时间和结束时间
        $startTime = $now->modify('-2 days');
        $endTime = $now->modify('-1 day');
        
        $this->userRankingList->setStartTime($startTime);
        $this->userRankingList->setEndTime($endTime);
        
        // 当前时间在结束时间之后，应该返回 false
        $this->assertFalse($this->userRankingList->isInValidPeriod($now));
    }
} 