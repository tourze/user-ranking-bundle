<?php

namespace UserRankingBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
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
#[WithMonologChannel(channel: 'user_ranking')]
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
        $isDryRun = (bool) $input->getArgument('dry-run');

        // 获取需要计算的排行榜
        $lists = [];
        if (null !== $listId) {
            $list = $this->listRepository->find($listId);
            if (null === $list) {
                assert(is_string($listId) || is_int($listId));
                $io->error(sprintf('排行榜 %s 不存在', (string) $listId));

                return Command::FAILURE;
            }
            $lists[] = $list;
        } else {
            $lists = $this->listRepository->findBy(['valid' => true]);
        }

        if (0 === count($lists)) {
            $io->warning('没有找到需要计算的排行榜');

            return Command::SUCCESS;
        }

        foreach ($lists as $list) {
            if ($list instanceof UserRankingList) {
                $this->calculateRanking($list, $io, $isDryRun);
            }
        }

        return Command::SUCCESS;
    }

    private function calculateRanking(UserRankingList $list, SymfonyStyle $io, bool $isDryRun): void
    {
        $io->section(sprintf('正在计算排行榜: %s', $list->getTitle()));

        if (!$this->shouldCalculateRanking($list, $io)) {
            return;
        }

        try {
            $this->performRankingCalculation($list, $io, $isDryRun);
        } catch (\Throwable $e) {
            $io->error(sprintf('计算失败: %s', $e->getMessage()));
        }
    }

    private function shouldCalculateRanking(UserRankingList $list, SymfonyStyle $io): bool
    {
        if (null === $list->getScoreSql()) {
            $io->warning('未配置计算SQL，跳过');

            return false;
        }

        if (!$this->isInValidTimePeriod($list)) {
            return false;
        }

        return true;
    }

    private function isInValidTimePeriod(UserRankingList $list): bool
    {
        if (null !== $list->getStartTime() && null !== $list->getEndTime()) {
            $now = CarbonImmutable::now();

            return !($now->lessThan($list->getStartTime()) || $now->greaterThan($list->getEndTime()));
        }

        return true;
    }

    private function performRankingCalculation(UserRankingList $list, SymfonyStyle $io, bool $isDryRun): void
    {
        $now = new \DateTimeImmutable();
        $blockedUserIds = $this->blacklistRepository->getBlockedUserIds($list, $now);
        $scoreSql = $list->getScoreSql();
        if (null === $scoreSql) {
            $this->handleEmptyScores($list, $io);

            return;
        }
        $scores = $this->connection->fetchAllAssociative($scoreSql);

        if ([] === $scores) {
            $this->handleEmptyScores($list, $io);

            return;
        }

        $validScores = $this->filterAndSortScores($this->normalizeScores($scores), $blockedUserIds);
        $fixedItems = $this->getFixedItems($list);
        $newRankings = $this->calculateNewRankings($list, $validScores, $fixedItems, $blockedUserIds, $io, $isDryRun);

        if (!$isDryRun) {
            $this->persistRankings($list, $newRankings, $io);
        } else {
            $io->note('空运行完成，未实际更新数据');
        }
    }

    private function handleEmptyScores(UserRankingList $list, SymfonyStyle $io): void
    {
        $qb = $this->itemRepository->createQueryBuilder('i');
        $qb->delete(UserRankingItem::class, 'i')
            ->where('i.list = :list')
            ->setParameter('list', $list)
            ->getQuery()
            ->execute()
        ;

        $list->updateRefreshTime();
        $this->entityManager->persist($list);
        $this->entityManager->flush();
        $io->warning('计算结果为空');
    }

    /**
     * @param array<array<string, mixed>> $scores
     * @return array<array{user_id: string, score: int}>
     */
    private function normalizeScores(array $scores): array
    {
        $normalized = [];
        foreach ($scores as $score) {
            if (isset($score['user_id'], $score['score'])) {
                $userId = $score['user_id'];
                $scoreValue = $score['score'];
                assert(is_string($userId) || is_int($userId));
                assert(is_string($scoreValue) || is_int($scoreValue));
                $normalized[] = [
                    'user_id' => (string) $userId,
                    'score' => (int) $scoreValue,
                ];
            }
        }

        return $normalized;
    }

    /**
     * @param array<array{user_id: string, score: int}> $scores
     * @param array<string> $blockedUserIds
     * @return array<array{user_id: string, score: int}>
     */
    private function filterAndSortScores(array $scores, array $blockedUserIds): array
    {
        $validScores = array_filter($scores, fn ($score) => !in_array($score['user_id'], $blockedUserIds, true));
        usort($validScores, fn ($a, $b) => $b['score'] - $a['score']);

        return $validScores;
    }

    /**
     * @return array<UserRankingItem>
     */
    private function getFixedItems(UserRankingList $list): array
    {
        return $this->itemRepository->findBy(['list' => $list, 'fixed' => true], ['number' => 'ASC']);
    }

    /**
     * @param array<array{user_id: string, score: int}> $scores
     * @param array<UserRankingItem> $fixedItems
     * @param array<string> $blockedUserIds
     * @return array<UserRankingItem>
     */
    private function calculateNewRankings(UserRankingList $list, array $scores, array $fixedItems, array $blockedUserIds, SymfonyStyle $io, bool $isDryRun): array
    {
        $fixedNumbers = array_map(fn (UserRankingItem $item): int => $item->getNumber() ?? 0, $fixedItems);
        $fixedUserNumbers = $this->buildFixedUserNumbersMap($fixedItems);
        $newRankings = $this->processFixedRankings($fixedItems, $blockedUserIds);

        $currentRank = 1;
        foreach ($scores as $score) {
            $userId = $score['user_id'];

            if (!$this->isUserValidForRanking($userId, $fixedUserNumbers)) {
                continue;
            }

            $currentRank = $this->findNextAvailableRank($currentRank, $fixedNumbers);

            if ($this->exceedsCountLimit($list, $currentRank)) {
                break;
            }

            if ($isDryRun) {
                $this->outputDryRunInfo($io, $userId, $currentRank, $score['score']);
            } else {
                $newRankings[] = $this->createRankingItem($list, $userId, $currentRank, $score['score']);
            }

            ++$currentRank;
        }

        return $newRankings;
    }

    /**
     * @param array<UserRankingItem> $fixedItems
     * @return array<string, int>
     */
    private function buildFixedUserNumbersMap(array $fixedItems): array
    {
        $fixedUserNumbers = [];
        foreach ($fixedItems as $item) {
            $userId = $item->getUserId();
            $number = $item->getNumber();
            if (null !== $userId && null !== $number) {
                $fixedUserNumbers[$userId] = $number;
            }
        }

        return $fixedUserNumbers;
    }

    /**
     * @param array<UserRankingItem> $fixedItems
     * @param array<string> $blockedUserIds
     * @return array<UserRankingItem>
     */
    private function processFixedRankings(array $fixedItems, array $blockedUserIds): array
    {
        $newRankings = [];
        foreach ($fixedItems as $item) {
            $userId = $item->getUserId();
            if (null !== $userId && !in_array($userId, $blockedUserIds, true)) {
                $newRankings[] = $item;
            }
        }

        return $newRankings;
    }

    /**
     * @param array<string, int> $fixedUserNumbers
     */
    private function isUserValidForRanking(string $userId, array $fixedUserNumbers): bool
    {
        try {
            $event = new RankingCalculateCheckUserEvent();
            $event->setUserId($userId);
            $this->eventDispatcher->dispatch($event);

            if ($event->isBlacklist()) {
                return false;
            }
        } catch (\Exception $exception) {
            $this->logger->error('检查用户黑名单失败', [
                'user_id' => $userId,
                'error' => $exception,
            ]);

            return false;
        }

        return !isset($fixedUserNumbers[$userId]);
    }

    /**
     * @param array<int> $fixedNumbers
     */
    private function findNextAvailableRank(int $currentRank, array $fixedNumbers): int
    {
        while (in_array($currentRank, $fixedNumbers, true)) {
            ++$currentRank;
        }

        return $currentRank;
    }

    private function exceedsCountLimit(UserRankingList $list, int $currentRank): bool
    {
        return null !== $list->getCount() && $currentRank > $list->getCount();
    }

    private function outputDryRunInfo(SymfonyStyle $io, string $userId, int $rank, int $score): void
    {
        $io->writeln(sprintf(
            '用户 %s 将被设置为第 %d 名，分数: %d',
            $userId,
            $rank,
            $score
        ));
    }

    private function createRankingItem(UserRankingList $list, string $userId, int $rank, int $score): UserRankingItem
    {
        $item = new UserRankingItem();
        $item->setList($list);
        $item->setUserId($userId);
        $item->setNumber($rank);
        $item->setScore($score);
        $item->setFixed(false);

        return $item;
    }

    /**
     * @param array<UserRankingItem> $newRankings
     */
    private function persistRankings(UserRankingList $list, array $newRankings, SymfonyStyle $io): void
    {
        $this->entityManager->beginTransaction();
        try {
            $this->clearExistingRankings($list);
            $this->saveNewRankings($newRankings);
            $this->updateListRefreshTime($list);

            $this->entityManager->flush();
            $this->entityManager->commit();
            $io->success('排名计算完成');

            $this->dispatchArchiveCommand($list);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function clearExistingRankings(UserRankingList $list): void
    {
        $qb = $this->itemRepository->createQueryBuilder('i');
        $qb->delete(UserRankingItem::class, 'i')
            ->where('i.list = :list')
            ->setParameter('list', $list)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param array<UserRankingItem> $newRankings
     */
    private function saveNewRankings(array $newRankings): void
    {
        foreach ($newRankings as $item) {
            $this->entityManager->persist($item);
        }
    }

    private function updateListRefreshTime(UserRankingList $list): void
    {
        $list->updateRefreshTime();
    }

    private function dispatchArchiveCommand(UserRankingList $list): void
    {
        $message = new RunCommandMessage();
        $message->setCommand(ArchiveRankingCommand::NAME);
        $message->setOptions([
            'list-id' => $list->getId(),
        ]);
        $this->messageBus->dispatch($message);
    }
}
