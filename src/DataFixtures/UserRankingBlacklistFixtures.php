<?php

declare(strict_types=1);

namespace UserRankingBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserRankingBlacklistFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const BLACKLIST_REFERENCE_PREFIX = 'user_ranking_blacklist_';
    public const BLACKLIST_COUNT = 5;

    public static function getGroups(): array
    {
        return ['user-ranking'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::BLACKLIST_COUNT; ++$i) {
            $blacklist = $this->createBlacklist($i);
            $manager->persist($blacklist);
            $this->addReference(self::BLACKLIST_REFERENCE_PREFIX . $i, $blacklist);
        }

        $manager->flush();
    }

    private function createBlacklist(int $index): UserRankingBlacklist
    {
        $list = $this->getReference(
            UserRankingListFixtures::LIST_REFERENCE_PREFIX . ($index % UserRankingListFixtures::LIST_COUNT),
            UserRankingList::class
        );

        if (!$list instanceof UserRankingList) {
            throw new \RuntimeException('Failed to get UserRankingList reference');
        }

        $blacklist = new UserRankingBlacklist();
        $blacklist->setUserId('blacklisted_user_' . (2000 + $index));
        $blacklist->setList($list);
        $blacklist->setReason('测试黑名单原因 ' . $index);
        $blacklist->setCreateTime(CarbonImmutable::now()->modify('-' . ($index + 1) . ' days'));
        $blacklist->setUpdateTime(CarbonImmutable::now()->modify('-' . $index . ' days'));
        $blacklist->setCreatedBy('admin');
        $blacklist->setUpdatedBy('admin');
        $blacklist->setValid(true);
        $blacklist->setUnblockTime(null);
        $blacklist->setExpireTime(CarbonImmutable::now()->modify('+30 days'));
        $blacklist->setComment('测试黑名单备注 ' . $index);

        return $blacklist;
    }

    public function getDependencies(): array
    {
        return [
            UserRankingListFixtures::class,
        ];
    }
}
