<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @method UserRankingList|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRankingList|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRankingList[]    findAll()
 * @method UserRankingList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRankingListRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingList::class);
    }
}
