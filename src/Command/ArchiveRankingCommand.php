<?php

namespace UserRankingBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Repository\UserRankingArchiveRepository;
use UserRankingBundle\Repository\UserRankingItemRepository;
use UserRankingBundle\Repository\UserRankingListRepository;

#[AsCommand(
    name: self::NAME,
    description: '归档指定排行榜的当前排名数据'
)]
class ArchiveRankingCommand extends Command
{
    public const NAME = 'user-ranking:archive';
    public const COMMAND = 'user-ranking:archive';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRankingListRepository $listRepository,
        private readonly UserRankingItemRepository $itemRepository,
        private readonly UserRankingArchiveRepository $archiveRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('list-id', InputArgument::REQUIRED, '排行榜ID')
            ->addOption('keep-days', 'k', InputOption::VALUE_OPTIONAL, '保留天数', 30)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listId = $input->getArgument('list-id');
        assert(is_string($listId) || is_int($listId) || null === $listId);
        $keepDaysOption = $input->getOption('keep-days') ?? 30;
        assert(is_string($keepDaysOption) || is_int($keepDaysOption));
        $keepDays = (int) $keepDaysOption;

        $list = $this->listRepository->find($listId);
        assert($list instanceof UserRankingList || null === $list);
        if (null === $list) {
            $output->writeln(sprintf('<error>排行榜 %s 不存在</error>', (string) $listId));

            return Command::FAILURE;
        }

        // 获取当前排名数据
        $items = $this->itemRepository->findBy(['list' => $list], ['number' => 'ASC']);
        if (0 === count($items)) {
            $output->writeln('<info>当前排行榜没有排名数据</info>');

            return Command::SUCCESS;
        }

        // TODO 暂时都是按天来归档
        $now = CarbonImmutable::now();

        // 把今天的删除
        $this->archiveRepository->createQueryBuilder('a')
            ->delete(UserRankingArchive::class, 'a')
            ->where('a.list = :list')
            ->andWhere('a.archiveTime > :archiveTime')
            ->setParameter('list', $list)
            ->setParameter('archiveTime', CarbonImmutable::now()->startOfDay())
            ->getQuery()
            ->execute()
        ;

        // 创建归档记录
        foreach ($items as $item) {
            /** @var UserRankingItem $item */
            $number = $item->getNumber();
            $userId = $item->getUserId();

            if (null === $number || null === $userId) {
                continue; // 跳过无效数据
            }

            $archive = new UserRankingArchive();
            $archive->setList($list);
            $archive->setNumber($number);
            $archive->setUserId($userId);
            $archive->setScore($item->getScore());
            $archive->setArchiveTime($now);

            $this->entityManager->persist($archive);
        }

        // 清理过期数据
        if ($keepDays > 0) {
            $expireDate = (new \DateTimeImmutable())->modify("-{$keepDays} days");
            $this->archiveRepository->createQueryBuilder('a')
                ->delete(UserRankingArchive::class, 'a')
                ->where('a.list = :list')
                ->andWhere('a.archiveTime < :archiveTime')
                ->setParameter('list', $list)
                ->setParameter('archiveTime', $expireDate)
                ->getQuery()
                ->execute()
            ;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('<info>成功归档 %d 条排名数据</info>', count($items)));

        return Command::SUCCESS;
    }
}
