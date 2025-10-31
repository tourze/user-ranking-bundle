<?php

namespace UserRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @internal
 */
#[CoversClass(UserRankingBlacklist::class)]
final class UserRankingBlacklistTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserRankingBlacklist();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $list = new UserRankingList();

        return [
            'valid' => ['valid', true],
            'userId' => ['userId', 'test_user'],
            'reason' => ['reason', 'test reason'],
            'comment' => ['comment', 'test comment'],
            // list 属性需要特殊处理，因为不能为 null
            // 'list' => ['list', $list],
        ];
    }

    private UserRankingBlacklist $blacklist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blacklist = new UserRankingBlacklist();
    }

    public function testGetterAndSetterForUser(): void
    {
        $userId = 'user123';
        $this->blacklist->setUserId($userId);
        $this->assertSame($userId, $this->blacklist->getUserId());
    }

    public function testGetterAndSetterForList(): void
    {
        // 使用真实的 Entity 对象而不是 mock
        // 理由1：Entity 测试应该测试真实的对象关系和行为
        // 理由2：Entity 对象通常不包含复杂的业务逻辑，使用真实对象更可靠
        // 理由3：避免 PHPStan 对 mock 具体类的限制，提高代码质量
        $list = new UserRankingList();
        $list->setTitle('测试排行榜');

        $this->blacklist->setList($list);
        $this->assertSame($list, $this->blacklist->getList());
    }

    public function testGetterAndSetterForReason(): void
    {
        $reason = '用户被禁止参与排行榜';
        $this->blacklist->setReason($reason);
        $this->assertEquals($reason, $this->blacklist->getReason());
    }

    public function testGetterAndSetterForCreateTime(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01');
        $this->blacklist->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->blacklist->getCreateTime());
    }

    public function testGetterAndSetterForUpdateTime(): void
    {
        $updateTime = new \DateTimeImmutable('2023-01-02');
        $this->blacklist->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->blacklist->getUpdateTime());
    }

    public function testGetterAndSetterForCreatedBy(): void
    {
        $createdBy = 'admin1';
        $this->blacklist->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->blacklist->getCreatedBy());
    }

    public function testGetterAndSetterForUpdatedBy(): void
    {
        $updatedBy = 'admin2';
        $this->blacklist->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->blacklist->getUpdatedBy());
    }

    public function testGetterAndSetterForValid(): void
    {
        // 默认值应该是 true
        $this->assertTrue($this->blacklist->isValid());

        // 设置为 true
        $this->blacklist->setValid(true);
        $this->assertTrue($this->blacklist->isValid());

        // 设置为 false
        $this->blacklist->setValid(false);
        $this->assertFalse($this->blacklist->isValid());

        // 设置为 null
        $this->blacklist->setValid(null);
        $this->assertNull($this->blacklist->isValid());
    }

    public function testGetterAndSetterForUnblockTime(): void
    {
        // 初始应该为空
        $this->assertNull($this->blacklist->getUnblockTime());

        // 设置解封时间
        $unblockTime = new \DateTimeImmutable('2023-05-01');
        $this->blacklist->setUnblockTime($unblockTime);
        $this->assertEquals($unblockTime, $this->blacklist->getUnblockTime());
    }

    public function testGetterAndSetterForExpireTime(): void
    {
        // 初始应该为空
        $this->assertNull($this->blacklist->getExpireTime());

        // 设置过期时间
        $expireTime = new \DateTimeImmutable('2023-05-01');
        $this->blacklist->setExpireTime($expireTime);
        $this->assertEquals($expireTime, $this->blacklist->getExpireTime());
    }

    public function testGetterAndSetterForComment(): void
    {
        $comment = '测试备注';
        $this->blacklist->setComment($comment);
        $this->assertEquals($comment, $this->blacklist->getComment());
    }

    public function testIsBlocked(): void
    {
        $now = new \DateTimeImmutable();

        // 没有设置解封时间，应该是已屏蔽的
        $this->assertTrue($this->blacklist->isBlocked($now));

        // 设置过去的解封时间，应该不再被屏蔽
        $pastTime = new \DateTimeImmutable();
        $pastTime = $pastTime->modify('-1 day');
        $this->blacklist->setUnblockTime($pastTime);
        $this->assertFalse($this->blacklist->isBlocked($now));

        // 设置未来的解封时间，应该仍然被屏蔽
        $futureTime = new \DateTimeImmutable();
        $futureTime = $futureTime->modify('+1 day');
        $this->blacklist->setUnblockTime($futureTime);
        $this->assertTrue($this->blacklist->isBlocked($now));
    }
}
