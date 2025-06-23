<?php

namespace UserRankingBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\AsyncCommandBundle\Message\RunCommandMessage;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingListRepository;

#[AsCronTask('* * * * *')]
#[AsCommand(
    name: self::NAME,
    description: '批量计算排行榜排名',
)]
class RefreshListCommand extends Command
{
    
    public const NAME = 'user-ranking:calculate';
public function __construct(
        private readonly UserRankingListRepository $listRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTimeImmutable();

        foreach ($this->listRepository->findBy(['valid' => true]) as $list) {
            if (!$this->shouldRefresh($list, $now)) {
                continue;
            }

            $message = new RunCommandMessage();
            $message->setCommand(RankingCalculateCommand::NAME);
            $message->setOptions([
                'list-id' => $list->getId(),
            ]);
            $this->messageBus->dispatch($message);
        }

        return Command::SUCCESS;
    }

    private function shouldRefresh(UserRankingList $list, \DateTimeImmutable $now): bool
    {
        // 获取最后刷新时间
        $lastRefresh = $list->getRefreshTime();
        if ($lastRefresh === null) {
            return true;
        }

        // 获取更新频率
        $frequency = $list->getRefreshFrequency();
        if ($frequency === null) {
            return false; // 如果没有设置更新频率，则不更新
        }

        // 计算时间间隔（秒）
        $interval = $now->getTimestamp() - $lastRefresh->getTimestamp();

        // 特殊处理每天更新的情况
        if (RefreshFrequency::DAILY === $frequency) {
            // 检查是否跨越了一天
            $lastDay = (int) $lastRefresh->format('Ymd');
            $currentDay = (int) $now->format('Ymd');

            return $lastDay !== $currentDay;
        }

        // 对于其他频率，检查是否超过了更新间隔
        // 为了避免错过更新，我们检查间隔是否在预期时间的正负30秒范围内
        $tolerance = 30; // 30秒的容差
        $normalizedInterval = $interval % $frequency->getSeconds();

        // 如果间隔在容差范围内，或者已经超过了一个完整周期，就需要更新
        return $normalizedInterval <= $tolerance || $interval >= $frequency->getSeconds();
    }
}
