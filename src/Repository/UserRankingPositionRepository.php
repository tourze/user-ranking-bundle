<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use UserRankingBundle\Entity\UserRankingPosition;

/**
 * @method UserRankingPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRankingPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRankingPosition[]    findAll()
 * @method UserRankingPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRankingPositionRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingPosition::class);
    }
}
