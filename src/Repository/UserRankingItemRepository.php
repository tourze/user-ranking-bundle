<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use UserRankingBundle\Entity\UserRankingItem;

/**
 * @method UserRankingItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRankingItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRankingItem[]    findAll()
 * @method UserRankingItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRankingItemRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingItem::class);
    }
}
