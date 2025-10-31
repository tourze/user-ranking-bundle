<?php

namespace UserRankingBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;

#[AsCronTask(expression: '*/5 * * * *')]
#[AsCommand(
    name: self::NAME,
    description: '清理已过期的排行榜黑名单记录',
)]
class BlacklistCleanupCommand extends Command
{
    public const NAME = 'user-ranking:blacklist-cleanup';

    public function __construct(
        private readonly UserRankingBlacklistRepository $blacklistRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTimeImmutable();

        try {
            // 查找已过期但仍然有效的黑名单记录
            $qb = $this->blacklistRepository->createQueryBuilder('b');
            $qb->update(UserRankingBlacklist::class, 'b')
                ->set('b.valid', ':valid')
                ->where('b.valid = true')
                ->andWhere('b.unblockTime IS NOT NULL')
                ->andWhere('b.unblockTime <= :now')
                ->setParameter('valid', false)
                ->setParameter('now', $now)
            ;

            $count = $qb->getQuery()->execute();
            assert(is_int($count));

            if ($count > 0) {
                $io->success(sprintf('成功清理 %d 条已过期的黑名单记录', $count));
            } else {
                $io->info('没有需要清理的黑名单记录');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error(sprintf('清理黑名单记录失败: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
