<?php

declare(strict_types=1);

namespace UserRankingBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserRankingListFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const LIST_REFERENCE_PREFIX = 'user_ranking_list_';
    public const LIST_COUNT = 3;

    public static function getGroups(): array
    {
        return ['user-ranking'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::LIST_COUNT; ++$i) {
            $list = $this->createList($i);
            $manager->persist($list);
            $this->addReference(self::LIST_REFERENCE_PREFIX . $i, $list);
        }

        $manager->flush();
    }

    private function createList(int $index): UserRankingList
    {
        $frequencies = [RefreshFrequency::HOURLY, RefreshFrequency::DAILY, RefreshFrequency::WEEKLY];
        $titles = ['积分排行榜', '贡献排行榜', '活跃度排行榜'];

        $list = new UserRankingList();
        $list->setTitle($titles[$index] ?? '测试排行榜 ' . $index);
        $list->setSubtitle('测试副标题 ' . $index);
        $list->setLogoUrl('/assets/ranking/logo' . $index . '.png');
        $list->setCount(100 + $index * 50);
        $list->setScoreSql('SELECT user_id, score FROM test_scores WHERE category = ' . $index);
        $list->setValid(true);
        $list->setRefreshFrequency($frequencies[$index]);
        $list->setCreateTime(CarbonImmutable::now()->modify('-' . ($index + 30) . ' days'));
        $list->setUpdateTime(CarbonImmutable::now()->modify('-' . $index . ' days'));
        $list->setCreatedBy('admin');
        $list->setUpdatedBy('admin');
        $list->setRefreshTime(CarbonImmutable::now()->modify('-1 hour'));

        $list->setStartTime(null);
        $list->setEndTime(null);

        $list->setColor('#' . sprintf('%06X', mt_rand(0, 0xFFFFFF)));

        return $list;
    }

    public function getDependencies(): array
    {
        return [
            UserRankingPositionFixtures::class,
        ];
    }
}
