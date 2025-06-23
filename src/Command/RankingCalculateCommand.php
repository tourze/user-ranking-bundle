<?php

namespace UserRankingBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\AsyncCommandBundle\Message\RunCommandMessage;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Event\RankingCalculateCheckUserEvent;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;
use UserRankingBundle\Repository\UserRankingItemRepository;
use UserRankingBundle\Repository\UserRankingListRepository;

#[AsCommand(
    name: self::NAME,
    description: '计算用户排行榜排名',
)]
class RankingCalculateCommand extends Command
{
    
    public const NAME = 'user-ranking:calculate';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserRankingListRepository $listRepository,
        private readonly UserRankingItemRepository $itemRepository,
        private readonly UserRankingBlacklistRepository $blacklistRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('list-id', InputArgument::OPTIONAL, description: '指定排行榜ID');
        $this->addArgument('dry-run', InputArgument::OPTIONAL, description: '空运行，不实际更新数据', default: 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $listId = $input->getArgument('list-id');
        $isDryRun = $input->getArgument('dry-run');

        // 获取需要计算的排行榜
        $lists = [];
        if ((bool) $listId) {
            $list = $this->listRepository->find($listId);
            if ($list === null) {
                $io->error(sprintf('排行榜 %s 不存在', $listId));

                return Command::FAILURE;
            }
            $lists[] = $list;
        } else {
            $lists = $this->listRepository->findBy(['valid' => true]);
        }

        if ((bool) empty($lists)) {
            $io->warning('没有找到需要计算的排行榜');

            return Command::SUCCESS;
        }

        foreach ($lists as $list) {
            $this->calculateRanking($list, $io, $isDryRun);
        }

        return Command::SUCCESS;
    }

    private function calculateRanking(UserRankingList $list, SymfonyStyle $io, bool $isDryRun): void
    {
        $io->section(sprintf('正在计算排行榜: %s', $list->getTitle()));

        // 检查是否有计算SQL
        if ($list->getScoreSql() === null) {
            $io->warning('未配置计算SQL，跳过');

            return;
        }

        if ($list->getStartTime() !== null && $list->getEndTime() !== null) {
            if (CarbonImmutable::now()->lessThan($list->getStartTime()) || CarbonImmutable::now()->greaterThan($list->getEndTime())) {
                return;
            }
        }

        try {
            $now = new \DateTimeImmutable();

            // 获取黑名单用户ID列表
            $blockedUserIds = $this->blacklistRepository->getBlockedUserIds($list, $now);

            // 执行计算SQL获取分数
            $scores = $this->connection->fetchAllAssociative($list->getScoreSql());

            if ((bool) empty($scores)) {
                // 为空的时候要把之前的数据都删掉
                $qb = $this->entityManager->createQueryBuilder();
                $qb->delete(UserRankingItem::class, 'i')
                    ->where('i.list = :list')
                    ->setParameter('list', $list)
                    ->getQuery()
                    ->execute();
                $list->updateRefreshTime();
                $this->entityManager->persist($list);
                $this->entityManager->flush();
                $io->warning('计算结果为空');

                return;
            }

            // 过滤黑名单用户并按分数排序
            $scores = array_filter($scores, fn ($score) => !in_array($score['user_id'], $blockedUserIds));
            usort($scores, fn ($a, $b) => $b['score'] - $a['score']);

            // 获取现有的固定排名
            $fixedItems = $this->itemRepository->findBy(['list' => $list, 'fixed' => true], ['number' => 'ASC']);

            $fixedNumbers = array_map(fn ($item) => $item->getNumber(), $fixedItems);
            // 记录固定排名用户的number映射
            $fixedUserNumbers = [];
            foreach ($fixedItems as $item) {
                $fixedUserNumbers[$item->getUserId()] = $item->getNumber();
            }

            // 开始分配排名
            $currentRank = 1;
            $processed = [];
            $newRankings = [];

            // 先处理固定排名
            foreach ($fixedItems as $item) {
                // 如果固定排名用户被拉黑，跳过处理
                if (in_array($item->getUserId(), $blockedUserIds)) {
                    continue;
                }
                $processed[$item->getUserId()] = true;
                // 保留固定排名记录
                $newRankings[] = $item;
            }

            // 处理动态排名
            foreach ($scores as $score) {
                $userId = $score['user_id'];
                try {
                    $event = new RankingCalculateCheckUserEvent();
                    $event->setUserId($userId);
                    $this->eventDispatcher->dispatch($event);
                    if ($event->isBlacklist()) {
                        continue;
                    }
                } catch (\Exception $exception) {
                    $this->logger->error('检查用户黑名单失败', [
                        'user_id' => $userId,
                        'error' => $exception,
                    ]);
                    continue;
                }

                // 如果用户有固定排名，使用固定排名
                if ((bool) isset($fixedUserNumbers[$userId])) {
                    continue;
                }

                // 找到下一个可用的排名
                while (in_array($currentRank, $fixedNumbers)) {
                    ++$currentRank;
                }

                // 检查是否超出总名次限制
                if ($list->getCount() !== null && $currentRank > $list->getCount()) {
                    break;
                }

                if ((bool) $isDryRun) {
                    $io->writeln(sprintf(
                        '用户 %s 将被设置为第 %d 名，分数: %d',
                        $userId,
                        $currentRank,
                        $score['score']
                    ));
                } else {
                    // 直接创建并存储 UserRankingItem 对象
                    $item = new UserRankingItem();
                    $item->setList($list)
                        ->setUserId($userId)
                        ->setNumber($currentRank)
                        ->setScore($score['score'])
                        ->setFixed(false);
                    $newRankings[] = $item;
                }

                ++$currentRank;
            }

            if (!$isDryRun) {
                // 开启事务
                $this->entityManager->beginTransaction();
                try {
                    // 删除所有记录（包括固定排名，因为我们已经在 $newRankings 中保存了它们）
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->delete(UserRankingItem::class, 'i')
                        ->where('i.list = :list')
                        ->setParameter('list', $list)
                        ->getQuery()
                        ->execute();

                    // 批量持久化所有排名记录（包括固定排名）
                    foreach ($newRankings as $item) {
                        $this->entityManager->persist($item);
                    }

                    // 更新刷新时间
                    $list->updateRefreshTime();
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                    $io->success('排名计算完成');

                    $message = new RunCommandMessage();
                    $message->setCommand(ArchiveRankingCommand::NAME);
                    $message->setOptions([
                        'list-id' => $list->getId(),
                    ]);
                    $this->messageBus->dispatch($message);
                } catch (\Throwable $e) {
                    $this->entityManager->rollback();
                    throw $e;
                }
            } else {
                $io->note('空运行完成，未实际更新数据');
            }
        } catch (\Throwable $e) {
            $io->error(sprintf('计算失败: %s', $e->getMessage()));
        }
    }
}
