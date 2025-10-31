<?php

namespace UserRankingBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use UserRankingBundle\Enum\RefreshFrequency;

/**
 * @internal
 */
#[CoversClass(RefreshFrequency::class)]
final class RefreshFrequencyTest extends AbstractEnumTestCase
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

    public function testGetBadge(): void
    {
        $this->assertEquals('info', RefreshFrequency::EVERY_MINUTE->getBadge());
        $this->assertEquals('info', RefreshFrequency::EVERY_FIVE_MINUTES->getBadge());
        $this->assertEquals('primary', RefreshFrequency::EVERY_FIFTEEN_MINUTES->getBadge());
        $this->assertEquals('primary', RefreshFrequency::EVERY_THIRTY_MINUTES->getBadge());
        $this->assertEquals('success', RefreshFrequency::HOURLY->getBadge());
        $this->assertEquals('success', RefreshFrequency::DAILY->getBadge());
        $this->assertEquals('warning', RefreshFrequency::WEEKLY->getBadge());
        $this->assertEquals('warning', RefreshFrequency::MONTHLY->getBadge());
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

    public function testGenOptions(): void
    {
        $options = RefreshFrequency::genOptions();
        $this->assertCount(8, $options);

        // 每个选项应该是一个含有label和value的数组
        $firstItem = $options[0];
        $this->assertArrayHasKey('value', $firstItem);
        $this->assertArrayHasKey('label', $firstItem);
        $this->assertContains('every_minute', array_column($options, 'value'));
        $this->assertContains('每分钟', array_column($options, 'label'));
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
        $this->assertSame(RefreshFrequency::EVERY_MINUTE, RefreshFrequency::from('every_minute'));

        // 测试相同枚举值的比较
        $everyMinute1 = RefreshFrequency::EVERY_MINUTE;
        $everyMinute2 = RefreshFrequency::EVERY_MINUTE;
        $this->assertSame($everyMinute1, $everyMinute2);
    }

    public function testToArray(): void
    {
        $result = RefreshFrequency::EVERY_MINUTE->toArray();
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('every_minute', $result['value']);
        $this->assertEquals('每分钟', $result['label']);

        $result = RefreshFrequency::DAILY->toArray();
        $this->assertEquals('daily', $result['value']);
        $this->assertEquals('每天', $result['label']);
    }
}
