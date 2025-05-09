<?php

namespace UserRankingBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Enum\RefreshFrequency;

class RefreshFrequencyTest extends TestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('每分钟', RefreshFrequency::EVERY_MINUTE->getLabel());
        $this->assertEquals('每5分钟', RefreshFrequency::EVERY_FIVE_MINUTES->getLabel());
        $this->assertEquals('每15分钟', RefreshFrequency::EVERY_FIFTEEN_MINUTES->getLabel());
        $this->assertEquals('每30分钟', RefreshFrequency::EVERY_THIRTY_MINUTES->getLabel());
        $this->assertEquals('每小时', RefreshFrequency::HOURLY->getLabel());
        $this->assertEquals('每天', RefreshFrequency::DAILY->getLabel());
        $this->assertEquals('每周', RefreshFrequency::WEEKLY->getLabel());
        $this->assertEquals('每月', RefreshFrequency::MONTHLY->getLabel());
    }

    public function testGetSeconds(): void
    {
        $this->assertEquals(60, RefreshFrequency::EVERY_MINUTE->getSeconds());
        $this->assertEquals(300, RefreshFrequency::EVERY_FIVE_MINUTES->getSeconds());
        $this->assertEquals(900, RefreshFrequency::EVERY_FIFTEEN_MINUTES->getSeconds());
        $this->assertEquals(1800, RefreshFrequency::EVERY_THIRTY_MINUTES->getSeconds());
        $this->assertEquals(3600, RefreshFrequency::HOURLY->getSeconds());
        $this->assertEquals(86400, RefreshFrequency::DAILY->getSeconds());
        $this->assertEquals(86400 * 7, RefreshFrequency::WEEKLY->getSeconds());
        $this->assertEquals(86400 * 30, RefreshFrequency::MONTHLY->getSeconds());
    }

    public function testCases(): void
    {
        $cases = RefreshFrequency::cases();
        $this->assertCount(8, $cases);
        $this->assertContains(RefreshFrequency::EVERY_MINUTE, $cases);
        $this->assertContains(RefreshFrequency::EVERY_FIVE_MINUTES, $cases);
        $this->assertContains(RefreshFrequency::EVERY_FIFTEEN_MINUTES, $cases);
        $this->assertContains(RefreshFrequency::EVERY_THIRTY_MINUTES, $cases);
        $this->assertContains(RefreshFrequency::HOURLY, $cases);
        $this->assertContains(RefreshFrequency::DAILY, $cases);
        $this->assertContains(RefreshFrequency::WEEKLY, $cases);
        $this->assertContains(RefreshFrequency::MONTHLY, $cases);
    }

    public function testGetItems(): void
    {
        $items = RefreshFrequency::getItems();
        $this->assertIsArray($items);
        $this->assertCount(8, $items);

        // 检查第一个项
        $firstItem = $items[0];
        $this->assertArrayHasKey('value', $firstItem);
        $this->assertArrayHasKey('label', $firstItem);
        $this->assertEquals('every_minute', $firstItem['value']);
        $this->assertEquals('每分钟', $firstItem['label']);
    }

    public function testGetOptions(): void
    {
        $options = RefreshFrequency::getOptions();
        $this->assertIsArray($options);
        $this->assertCount(8, $options);

        // 每个选项应该是一个键值对，其中键是枚举值，值是对应的标签
        $this->assertArrayHasKey('every_minute', $options);
        $this->assertEquals('每分钟', $options['every_minute']);
        $this->assertArrayHasKey('daily', $options);
        $this->assertEquals('每天', $options['daily']);
    }

    public function testFromName(): void
    {
        $enum = RefreshFrequency::from('every_minute');
        $this->assertSame(RefreshFrequency::EVERY_MINUTE, $enum);

        $enum = RefreshFrequency::from('daily');
        $this->assertSame(RefreshFrequency::DAILY, $enum);
    }

    public function testFromInvalidName(): void
    {
        $this->expectException(\ValueError::class);
        RefreshFrequency::from('invalid_value');
    }

    public function testTryFromName(): void
    {
        $enum = RefreshFrequency::tryFrom('every_minute');
        $this->assertSame(RefreshFrequency::EVERY_MINUTE, $enum);

        $enum = RefreshFrequency::tryFrom('invalid_value');
        $this->assertNull($enum);
    }

    public function testCompareWithStringValue(): void
    {
        $this->assertTrue(RefreshFrequency::EVERY_MINUTE === RefreshFrequency::from('every_minute'));
        $this->assertFalse(RefreshFrequency::EVERY_MINUTE === RefreshFrequency::DAILY);
    }
} 