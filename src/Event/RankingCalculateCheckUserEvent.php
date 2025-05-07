<?php

namespace UserRankingBundle\Event;

class RankingCalculateCheckUserEvent
{
    private int|string $userId;

    private bool $isBlacklist = false;

    public function getUserId(): int|string
    {
        return $this->userId;
    }

    public function setUserId(int|string $userId): void
    {
        $this->userId = $userId;
    }

    public function isBlacklist(): bool
    {
        return $this->isBlacklist;
    }

    public function setIsBlacklist(bool $isBlacklist): void
    {
        $this->isBlacklist = $isBlacklist;
    }
}
