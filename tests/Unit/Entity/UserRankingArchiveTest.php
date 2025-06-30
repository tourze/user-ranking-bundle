<?php

namespace UserRankingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;

class UserRankingArchiveTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $archive = new UserRankingArchive();
        
        $list = new UserRankingList();
        $archive->setList($list);
        $this->assertSame($list, $archive->getList());
        
        $archive->setNumber(1);
        $this->assertEquals(1, $archive->getNumber());
        
        $archive->setUserId('123');
        $this->assertEquals('123', $archive->getUserId());
        
        $archive->setScore(100);
        $this->assertEquals(100, $archive->getScore());
        
        $archiveTime = new \DateTimeImmutable();
        $archive->setArchiveTime($archiveTime);
        $this->assertSame($archiveTime, $archive->getArchiveTime());
    }

    public function testToString(): void
    {
        $archive = new UserRankingArchive();
        
        // ID 初始为 0 时应返回 '0'
        $this->assertEquals('0', $archive->__toString());
    }

    public function testFluentInterface(): void
    {
        $archive = new UserRankingArchive();
        $list = new UserRankingList();
        $archiveTime = new \DateTimeImmutable();
        
        $result = $archive->setList($list)
                         ->setNumber(1)
                         ->setUserId('123')
                         ->setScore(100)
                         ->setArchiveTime($archiveTime);
        
        $this->assertSame($archive, $result);
    }
}