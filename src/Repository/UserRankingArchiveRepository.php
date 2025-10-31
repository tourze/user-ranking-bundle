<?php

declare(strict_types=1);

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @extends ServiceEntityRepository<UserRankingArchive>
 */
#[AsRepository(entityClass: UserRankingArchive::class)]
class UserRankingArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingArchive::class);
    }

    public function save(UserRankingArchive $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserRankingArchive $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<UserRankingArchive>
     */
    public function findListHistoryRanking(UserRankingList $list, \DateTimeInterface $archiveTime): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.list = :list')
            ->andWhere('a.archiveTime = :archiveTime')
            ->setParameter('list', $list)
            ->setParameter('archiveTime', $archiveTime)
            ->orderBy('a.number', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var array<UserRankingArchive> */
    }
}
