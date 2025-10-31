<?php

declare(strict_types=1);

namespace UserRankingBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserRankingBundle\Entity\UserRankingArchive;

#[AdminCrud(routePath: '/user-ranking/archive', routeName: 'user_ranking_archive')]
final class UserRankingArchiveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRankingArchive::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜归档')
            ->setEntityLabelInPlural('排行榜归档')
            ->setPageTitle('index', '排行榜归档列表')
            ->setPageTitle('detail', '排行榜归档详情')
            ->setPageTitle('edit', '编辑排行榜归档')
            ->setPageTitle('new', '新建排行榜归档')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield AssociationField::new('list', '排行榜')
            ->setHelp('所属排行榜')
        ;

        yield IntegerField::new('number', '排名')
            ->setHelp('用户在排行榜中的历史排名')
        ;

        yield TextField::new('userId', '用户ID')
            ->setHelp('用户ID，必填，最多255个字符')
        ;

        yield IntegerField::new('score', '分数')
            ->setHelp('用户的历史排行榜分数，可选')
        ;

        yield DateTimeField::new('archiveTime', '归档时间')
            ->setHelp('排行榜数据的归档时间，必填')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('list')
            ->add('userId')
            ->add('archiveTime')
        ;
    }
}
