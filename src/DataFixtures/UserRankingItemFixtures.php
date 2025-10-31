<?php

declare(strict_types=1);

namespace UserRankingBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserRankingItemFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const ITEM_REFERENCE_PREFIX = 'user_ranking_item_';
    public const ITEM_COUNT = 30;

    public static function getGroups(): array
    {
        return ['user-ranking'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::ITEM_COUNT; ++$i) {
            $item = $this->createItem($i);
            $manager->persist($item);
            $this->addReference(self::ITEM_REFERENCE_PREFIX . $i, $item);
        }

        $manager->flush();
    }

    private function createItem(int $index): UserRankingItem
    {
        $list = $this->getReference(
            UserRankingListFixtures::LIST_REFERENCE_PREFIX . ($index % UserRankingListFixtures::LIST_COUNT),
            UserRankingList::class
        );

        if (!$list instanceof UserRankingList) {
            throw new \RuntimeException('Failed to get UserRankingList reference');
        }

        $item = new UserRankingItem();
        $item->setList($list);
        $item->setNumber($index % 10 + 1);
        $item->setUserId('user_' . (3000 + $index));
        $item->setScore(2000 - $index * 15);
        $item->setCreateTime(CarbonImmutable::now()->modify('-' . ($index % 7) . ' days'));
        $item->setUpdateTime(CarbonImmutable::now()->modify('-' . ($index % 3) . ' days'));

        return $item;
    }

    public function getDependencies(): array
    {
        return [
            UserRankingListFixtures::class,
        ];
    }
}
