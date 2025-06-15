<?php

namespace UserRankingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\AsyncCommandBundle\AsyncCommandBundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\LockServiceBundle\LockServiceBundle;
use Tourze\Symfony\CronJob\CronJobBundle;

class UserRankingBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            AsyncCommandBundle::class => ['all' => true],
            DoctrineSnowflakeBundle::class => ['all' => true],
            DoctrineIndexedBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineTrackBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            LockServiceBundle::class => ['all' => true],
            CronJobBundle::class => ['all' => true],
        ];
    }
}
