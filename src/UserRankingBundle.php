<?php

namespace UserRankingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '用户排行榜')]
class UserRankingBundle extends Bundle
{
}
