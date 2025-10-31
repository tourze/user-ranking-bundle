<?php

declare(strict_types=1);

namespace UserRankingBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserRankingArchiveFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const ARCHIVE_REFERENCE_PREFIX = 'user_ranking_archive_';
    public const ARCHIVE_COUNT = 20;

    public static function getGroups(): array
    {
        return ['user-ranking'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::ARCHIVE_COUNT; ++$i) {
            $archive = $this->createArchive($i);
            $manager->persist($archive);
            $this->addReference(self::ARCHIVE_REFERENCE_PREFIX . $i, $archive);
        }

        $manager->flush();
    }

    private function createArchive(int $index): UserRankingArchive
    {
        $list = $this->getReference(
            UserRankingListFixtures::LIST_REFERENCE_PREFIX . ($index % UserRankingListFixtures::LIST_COUNT),
            UserRankingList::class
        );

        if (!$list instanceof UserRankingList) {
            throw new \RuntimeException('Failed to get UserRankingList reference');
        }

        $archive = new UserRankingArchive();
        $archive->setList($list);
        $archive->setNumber($index % 10 + 1);
        $archive->setUserId('user_' . (1000 + $index));
        $archive->setScore(1000 - $index * 10);
        $archive->setArchiveTime(CarbonImmutable::now()->modify('-' . ($index + 1) . ' days'));

        return $archive;
    }

    public function getDependencies(): array
    {
        return [
            UserRankingListFixtures::class,
        ];
    }
}
