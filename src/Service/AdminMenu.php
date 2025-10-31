<?php

declare(strict_types=1);

namespace UserRankingBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use UserRankingBundle\Entity\UserRankingArchive;
use UserRankingBundle\Entity\UserRankingBlacklist;
use UserRankingBundle\Entity\UserRankingItem;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Entity\UserRankingPosition;

/**
 * 用户排行榜管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('业务管理')) {
            $item->addChild('业务管理');
        }

        $businessMenu = $item->getChild('业务管理');
        if (null === $businessMenu) {
            return;
        }

        // 添加排行榜管理子菜单
        if (null === $businessMenu->getChild('排行榜管理')) {
            $businessMenu->addChild('排行榜管理')
                ->setAttribute('icon', 'fas fa-trophy')
            ;
        }

        $rankingMenu = $businessMenu->getChild('排行榜管理');
        if (null === $rankingMenu) {
            return;
        }

        $rankingMenu->addChild('排行榜列表')
            ->setUri($this->linkGenerator->getCurdListPage(UserRankingList::class))
            ->setAttribute('icon', 'fas fa-list')
        ;

        $rankingMenu->addChild('排行榜项目')
            ->setUri($this->linkGenerator->getCurdListPage(UserRankingItem::class))
            ->setAttribute('icon', 'fas fa-user-friends')
        ;

        $rankingMenu->addChild('职位管理')
            ->setUri($this->linkGenerator->getCurdListPage(UserRankingPosition::class))
            ->setAttribute('icon', 'fas fa-briefcase')
        ;

        $rankingMenu->addChild('黑名单管理')
            ->setUri($this->linkGenerator->getCurdListPage(UserRankingBlacklist::class))
            ->setAttribute('icon', 'fas fa-user-times')
        ;

        $rankingMenu->addChild('历史归档')
            ->setUri($this->linkGenerator->getCurdListPage(UserRankingArchive::class))
            ->setAttribute('icon', 'fas fa-archive')
        ;
    }
}
