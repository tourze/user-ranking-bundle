<?php

namespace UserRankingBundle\Command;

use Carbon\Carbon;
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
use UserRankingBundle\Repository\UserRankingItemRepository;
use UserRankingBundle\Repository\UserRankingListRepository;

#[AsCommand(
    name: 'user-ranking:archive',
    description: '归档指定排行榜的当前排名数据'
)]
class ArchiveRankingCommand extends Command
{
    public const COMMAND = 'user-ranking:archive';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRankingListRepository $listRepository,
        private readonly UserRankingItemRepository $itemRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('list-id', InputArgument::REQUIRED, '排行榜ID')
            ->addOption('keep-days', 'k', InputOption::VALUE_OPTIONAL, '保留天数', 30);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listId = $input->getArgument('list-id');
        $keepDays = (int) $input->getOption('keep-days');

        /** @var UserRankingList|null $list */
        $list = $this->listRepository->find($listId);
        if (!$list) {
            $output->writeln(sprintf('<error>排行榜 %s 不存在</error>', $listId));

            return Command::FAILURE;
        }

        // 获取当前排名数据
        $items = $this->itemRepository->findBy(['list' => $list], ['number' => 'ASC']);
        if (empty($items)) {
            $output->writeln('<info>当前排行榜没有排名数据</info>');

            return Command::SUCCESS;
        }

        // TODO 暂时都是按天来归档
        $now = Carbon::now();

        // 把今天的删除
        $this->entityManager->createQueryBuilder()
            ->delete(UserRankingArchive::class, 'a')
            ->where('a.list = :list')
            ->andWhere('a.archiveTime > :archiveTime')
            ->setParameter('list', $list)
            ->setParameter('archiveTime', Carbon::now()->startOfDay())
            ->getQuery()
            ->execute();

        // 创建归档记录
        foreach ($items as $item) {
            /** @var UserRankingItem $item */
            $archive = new UserRankingArchive();
            $archive->setList($list)
                ->setNumber($item->getNumber())
                ->setUserId($item->getUserId())
                ->setScore($item->getScore())
                ->setArchiveTime($now);

            $this->entityManager->persist($archive);
        }

        // 清理过期数据
        if ($keepDays > 0) {
            $expireDate = (new \DateTimeImmutable())->modify("-{$keepDays} days");
            $this->entityManager->createQueryBuilder()
                ->delete(UserRankingArchive::class, 'a')
                ->where('a.list = :list')
                ->andWhere('a.archiveTime < :archiveTime')
                ->setParameter('list', $list)
                ->setParameter('archiveTime', $expireDate)
                ->getQuery()
                ->execute();
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('<info>成功归档 %d 条排名数据</info>', count($items)));

        return Command::SUCCESS;
    }
}
