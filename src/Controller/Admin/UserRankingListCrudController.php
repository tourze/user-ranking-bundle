<?php

declare(strict_types=1);

namespace UserRankingBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;

#[AdminCrud(routePath: '/user-ranking/list', routeName: 'user_ranking_list')]
final class UserRankingListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRankingList::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜')
            ->setEntityLabelInPlural('排行榜')
            ->setPageTitle('index', '排行榜列表')
            ->setPageTitle('detail', '排行榜详情')
            ->setPageTitle('edit', '编辑排行榜')
            ->setPageTitle('new', '新建排行榜')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('title', '标题')
            ->setHelp('排行榜标题，必填，最多64个字符')
        ;

        yield TextField::new('subtitle', '副标题')
            ->setHelp('排行榜副标题，可选，最多100个字符')
        ;

        yield ColorField::new('color', '颜色')
            ->setHelp('排行榜主题颜色，必填，最多20个字符')
        ;

        yield UrlField::new('logoUrl', 'LOGO地址')
            ->setHelp('排行榜LOGO图片地址，可选，最多255个字符')
        ;

        yield DateTimeField::new('startTime', '开始时间')
            ->setHelp('排行榜生效开始时间，可选')
        ;

        yield DateTimeField::new('endTime', '结束时间')
            ->setHelp('排行榜生效结束时间，可选')
        ;

        yield TextareaField::new('scoreSql', '计算SQL')
            ->setHelp('用于计算排行榜分数的SQL语句，可选')
            ->hideOnIndex()
        ;

        yield IntegerField::new('count', '总名次')
            ->setHelp('排行榜总名次数量，可选')
            ->hideOnForm()
        ;

        yield ChoiceField::new('refreshFrequency', '更新频率')
            ->setChoices([
                '每分钟' => RefreshFrequency::EVERY_MINUTE,
                '每5分钟' => RefreshFrequency::EVERY_FIVE_MINUTES,
                '每15分钟' => RefreshFrequency::EVERY_FIFTEEN_MINUTES,
                '每30分钟' => RefreshFrequency::EVERY_THIRTY_MINUTES,
                '每小时' => RefreshFrequency::HOURLY,
                '每天' => RefreshFrequency::DAILY,
                '每周' => RefreshFrequency::WEEKLY,
                '每月' => RefreshFrequency::MONTHLY,
            ])
            ->setHelp('排行榜数据刷新频率')
            ->renderAsBadges([
                RefreshFrequency::EVERY_MINUTE->value => 'info',
                RefreshFrequency::EVERY_FIVE_MINUTES->value => 'info',
                RefreshFrequency::EVERY_FIFTEEN_MINUTES->value => 'primary',
                RefreshFrequency::EVERY_THIRTY_MINUTES->value => 'primary',
                RefreshFrequency::HOURLY->value => 'success',
                RefreshFrequency::DAILY->value => 'success',
                RefreshFrequency::WEEKLY->value => 'warning',
                RefreshFrequency::MONTHLY->value => 'warning',
            ])
        ;

        yield DateTimeField::new('refreshTime', '最后刷新时间')
            ->setHelp('最后一次刷新排行榜数据的时间')
            ->hideOnForm()
        ;

        yield BooleanField::new('valid', '是否有效')
            ->setHelp('排行榜是否有效')
        ;

        yield AssociationField::new('positions', '关联职位')
            ->setHelp('与排行榜关联的职位信息')
            ->hideOnIndex()
        ;

        yield AssociationField::new('items', '排行榜项目')
            ->setHelp('排行榜中的具体项目')
            ->hideOnIndex()
            ->hideOnForm()
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
            ->add('refreshFrequency')
            ->add('startTime')
            ->add('endTime')
        ;
    }
}
