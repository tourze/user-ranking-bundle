<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;

class UserRankingArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingArchive::class);
    }

    public function findListHistoryRanking(UserRankingList $list, \DateTimeInterface $archiveTime): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.list = :list')
            ->andWhere('a.archiveTime = :archiveTime')
            ->setParameter('list', $list)
            ->setParameter('archiveTime', $archiveTime)
            ->orderBy('a.number', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
