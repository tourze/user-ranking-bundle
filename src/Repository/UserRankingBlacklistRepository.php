<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @method UserRankingBlacklist|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRankingBlacklist|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRankingBlacklist[]    findAll()
 * @method UserRankingBlacklist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRankingBlacklistRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingBlacklist::class);
    }

    /**
     * 获取当前被拉黑的用户ID列表
     */
    public function getBlockedUserIds(UserRankingList $list, \DateTimeInterface $now): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('IDENTITY(b.bizUser) as userId')
            ->where('b.list = :list')
            ->andWhere('b.valid = true')
            ->andWhere('b.unblockTime IS NULL OR b.unblockTime > :now')
            ->setParameter('list', $list)
            ->setParameter('now', $now);

        return array_column($qb->getQuery()->getArrayResult(), 'userId');
    }
}
