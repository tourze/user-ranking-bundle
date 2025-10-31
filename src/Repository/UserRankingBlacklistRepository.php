<?php

namespace UserRankingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingList;

/**
 * @extends ServiceEntityRepository<UserRankingBlacklist>
 */
#[AsRepository(entityClass: UserRankingBlacklist::class)]
class UserRankingBlacklistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRankingBlacklist::class);
    }

    public function save(UserRankingBlacklist $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserRankingBlacklist $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据用户ID和排行榜查找黑名单记录
     */
    public function findByUserIdAndList(string $userId, UserRankingList $list): ?UserRankingBlacklist
    {
        $result = $this->findOneBy([
            'userId' => $userId,
            'list' => $list,
            'valid' => true,
        ]);

        return $result instanceof UserRankingBlacklist ? $result : null;
    }

    /**
     * 清理已过期的黑名单记录
     *
     * @return int 被移除的记录数量
     */
    public function removeExpired(): int
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('b')
            ->delete()
            ->where('b.expireTime IS NOT NULL')
            ->andWhere('b.expireTime < :now')
            ->setParameter('now', $now)
        ;

        $result = $qb->getQuery()->execute();
        assert(is_int($result));

        return $result;
    }

    /**
     * 获取当前被拉黑的用户ID列表
     *
     * @return array<string>
     */
    public function getBlockedUserIds(UserRankingList $list, \DateTimeInterface $now): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.userId')
            ->where('b.list = :list')
            ->andWhere('b.valid = true')
            ->andWhere('b.unblockTime IS NULL OR b.unblockTime > :now')
            ->setParameter('list', $list)
            ->setParameter('now', $now)
        ;

        return array_column($qb->getQuery()->getArrayResult(), 'userId');
    }
}
