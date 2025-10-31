<?php

namespace UserRankingBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UserRankingBundle\Event\RankingCalculateCheckUserEvent;

/**
 * @internal
 */
#[CoversClass(RankingCalculateCheckUserEvent::class)]
final class RankingCalculateCheckUserEventTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $event = new RankingCalculateCheckUserEvent();

        // isBlacklist 默认应为 false
        $this->assertFalse($event->isBlacklist());
    }
}
