<?php

declare(strict_types=1);

namespace UserRankingBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserRankingBundle\Entity\UserRankingItem;

#[AdminCrud(routePath: '/user-ranking/item', routeName: 'user_ranking_item')]
final class UserRankingItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRankingItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜项目')
            ->setEntityLabelInPlural('排行榜项目')
            ->setPageTitle('index', '排行榜项目列表')
            ->setPageTitle('detail', '排行榜项目详情')
            ->setPageTitle('edit', '编辑排行榜项目')
            ->setPageTitle('new', '新建排行榜项目')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield AssociationField::new('list', '排行榜')
            ->setHelp('所属排行榜')
        ;

        yield IntegerField::new('number', '排名')
            ->setHelp('用户在排行榜中的排名，必填')
        ;

        yield TextField::new('userId', '用户ID')
            ->setHelp('用户ID，必填，最多20个字符')
        ;

        yield TextField::new('textReason', '上榜理由')
            ->setHelp('用户上榜的理由说明，可选，最多500个字符')
            ->hideOnIndex()
        ;

        yield IntegerField::new('score', '分数')
            ->setHelp('用户的排行榜分数，可选')
        ;

        yield BooleanField::new('fixed', '固定排名')
            ->setHelp('是否为固定排名，不参与自动计算')
        ;

        yield TextField::new('recommendThumb', '推荐人头像')
            ->setHelp('推荐人头像地址，可选，最多255个字符')
            ->hideOnIndex()
        ;

        yield TextareaField::new('recommendReason', '推荐理由')
            ->setHelp('推荐理由详细描述，可选')
            ->hideOnIndex()
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setHelp('排行榜项目是否有效')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;

        yield TextField::new('createdBy', '创建者')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield TextField::new('updatedBy', '更新者')
            ->hideOnForm()
            ->hideOnIndex()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('list')
            ->add('userId')
            ->add('valid')
            ->add('number')
        ;
    }
}
