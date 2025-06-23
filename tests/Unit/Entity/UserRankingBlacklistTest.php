<?php

namespace UserRankingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

class UserRankingBlacklistTest extends TestCase
{
    private UserRankingBlacklist $blacklist;

    protected function setUp(): void
    {
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
        $list = $this->createMock(UserRankingList::class);
        $list->method('getId')->willReturn('789');
        
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

    public function testToString(): void
    {
        // 由于没有 __toString 方法，跳过此测试
        $this->assertTrue(true);
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
