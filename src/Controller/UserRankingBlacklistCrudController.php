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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserRankingBundle\Entity\UserRankingBlacklist;

#[AdminCrud(routePath: '/user-ranking/blacklist', routeName: 'user_ranking_blacklist')]
final class UserRankingBlacklistCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRankingBlacklist::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜黑名单')
            ->setEntityLabelInPlural('排行榜黑名单')
            ->setPageTitle('index', '排行榜黑名单列表')
            ->setPageTitle('detail', '排行榜黑名单详情')
            ->setPageTitle('edit', '编辑排行榜黑名单')
            ->setPageTitle('new', '新建排行榜黑名单')
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

        yield TextField::new('userId', '用户ID')
            ->setHelp('被拉黑的用户ID，必填，最多64个字符')
        ;

        yield TextareaField::new('reason', '拉黑原因')
            ->setHelp('拉黑的原因说明，可选')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('unblockTime', '解封时间')
            ->setHelp('用户解封时间，可选')
        ;

        yield DateTimeField::new('expireTime', '过期时间')
            ->setHelp('黑名单过期时间，可选')
            ->hideOnIndex()
        ;

        yield TextareaField::new('comment', '备注')
            ->setHelp('额外备注信息，可选')
            ->hideOnIndex()
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setHelp('黑名单是否有效')
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
            ->add('unblockTime')
        ;
    }
}
