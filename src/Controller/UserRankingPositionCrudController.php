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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserRankingBundle\Entity\UserRankingPosition;

#[AdminCrud(routePath: '/user-ranking/position', routeName: 'user_ranking_position')]
final class UserRankingPositionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRankingPosition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜职位')
            ->setEntityLabelInPlural('排行榜职位')
            ->setPageTitle('index', '排行榜职位列表')
            ->setPageTitle('detail', '排行榜职位详情')
            ->setPageTitle('edit', '编辑排行榜职位')
            ->setPageTitle('new', '新建排行榜职位')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('title', '名称')
            ->setHelp('职位名称，必须唯一，最多50个字符')
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setHelp('职位是否有效')
        ;

        yield AssociationField::new('lists', '关联排行榜')
            ->setHelp('与此职位关联的排行榜')
            ->hideOnIndex()
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
            ->add('title')
            ->add('valid')
        ;
    }
}
