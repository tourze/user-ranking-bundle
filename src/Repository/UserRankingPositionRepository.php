<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use UserRankingBundle\Entity\UserRankingPosition;

/**
 * @extends ServiceEntityRepository<UserRankingPosition>
 */
#[AsRepository(entityClass: UserRankingPosition::class)]
class UserRankingPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingPosition::class);
    }

    public function save(UserRankingPosition $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserRankingPosition $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
