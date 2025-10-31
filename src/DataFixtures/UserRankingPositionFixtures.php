<?php

declare(strict_types=1);

namespace UserRankingBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserRankingBundle\Entity\UserRankingPosition;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserRankingPositionFixtures extends Fixture implements FixtureGroupInterface
{
    public const POSITION_REFERENCE_PREFIX = 'user_ranking_position_';
    public const POSITION_COUNT = 5;

    public static function getGroups(): array
    {
        return ['user-ranking'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::POSITION_COUNT; ++$i) {
            $position = $this->createPosition($i);
            $manager->persist($position);
            $this->addReference(self::POSITION_REFERENCE_PREFIX . $i, $position);
        }

        $manager->flush();
    }

    private function createPosition(int $index): UserRankingPosition
    {
        $names = ['钻石', '黄金', '白银', '青铜', '新手'];

        $position = new UserRankingPosition();
        $position->setTitle($names[$index] ?? '等级 ' . $index);
        $position->setValid(true);
        $position->setCreateTime(CarbonImmutable::now()->modify('-' . ($index + 60) . ' days'));
        $position->setUpdateTime(CarbonImmutable::now()->modify('-' . $index . ' days'));
        $position->setCreatedBy('admin');
        $position->setUpdatedBy('admin');

        return $position;
    }
}
