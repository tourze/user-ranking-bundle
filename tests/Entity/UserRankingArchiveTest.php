<?php

namespace UserRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @internal
 */
#[CoversClass(UserRankingArchive::class)]
final class UserRankingArchiveTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserRankingArchive();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $list = new UserRankingList();

        return [
            'list' => ['list', $list],
            'number' => ['number', 123],
            'userId' => ['userId', 'test_value'],
        ];
    }

    public function testToString(): void
    {
        $archive = new UserRankingArchive();

        // ID 初始为 0 时应返回 '0'
        $this->assertEquals('0', $archive->__toString());
    }

    public function testSettersAndGetters(): void
    {
        $archive = new UserRankingArchive();
        $list = new UserRankingList();
        $archiveTime = new \DateTimeImmutable();

        // Test setters and verify the values were set correctly
        $archive->setList($list);
        $archive->setNumber(1);
        $archive->setUserId('123');
        $archive->setScore(100);
        $archive->setArchiveTime($archiveTime);

        // Verify the values were set correctly
        $this->assertSame($list, $archive->getList());
        $this->assertSame(1, $archive->getNumber());
        $this->assertSame('123', $archive->getUserId());
        $this->assertSame(100, $archive->getScore());
        $this->assertSame($archiveTime, $archive->getArchiveTime());
    }
}
