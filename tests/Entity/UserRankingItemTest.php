<?php

namespace UserRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @internal
 */
#[CoversClass(UserRankingItem::class)]
final class UserRankingItemTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserRankingItem();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'id' => ['id', '1234567890123456789'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
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

    public function testSettersAndGetters(): void
    {
        $item = new UserRankingItem();
        $list = new UserRankingList();

        // Test setters and verify the values were set correctly
        $item->setValid(true);
        $item->setList($list);
        $item->setNumber(1);
        $item->setUserId('123');
        $item->setTextReason('Test reason');
        $item->setScore(100);
        $item->setFixed(true);
        $item->setRecommendThumb('thumb.jpg');
        $item->setRecommendReason('Recommend reason');

        // Verify the values were set correctly
        $this->assertTrue($item->isValid());
        $this->assertSame($list, $item->getList());
        $this->assertSame(1, $item->getNumber());
        $this->assertSame('123', $item->getUserId());
        $this->assertSame('Test reason', $item->getTextReason());
        $this->assertSame(100, $item->getScore());
        $this->assertTrue($item->isFixed());
        $this->assertSame('thumb.jpg', $item->getRecommendThumb());
        $this->assertSame('Recommend reason', $item->getRecommendReason());
    }
}
