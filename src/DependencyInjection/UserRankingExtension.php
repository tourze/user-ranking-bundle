<?php

namespace UserRankingBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class UserRankingExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
