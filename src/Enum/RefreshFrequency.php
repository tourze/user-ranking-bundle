<?php

namespace UserRankingBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum RefreshFrequency: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case EVERY_MINUTE = 'every_minute';
    case EVERY_FIVE_MINUTES = 'every_five_minutes';
    case EVERY_FIFTEEN_MINUTES = 'every_fifteen_minutes';
    case EVERY_THIRTY_MINUTES = 'every_thirty_minutes';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::EVERY_MINUTE => '每分钟',
            self::EVERY_FIVE_MINUTES => '每5分钟',
            self::EVERY_FIFTEEN_MINUTES => '每15分钟',
            self::EVERY_THIRTY_MINUTES => '每30分钟',
            self::HOURLY => '每小时',
            self::DAILY => '每天',
            self::WEEKLY => '每周',
            self::MONTHLY => '每月',
        };
    }

    public function getSeconds(): int
    {
        return match ($this) {
            self::EVERY_MINUTE => 60,
            self::EVERY_FIVE_MINUTES => 300,
            self::EVERY_FIFTEEN_MINUTES => 900,
            self::EVERY_THIRTY_MINUTES => 1800,
            self::HOURLY => 3600,
            self::DAILY => 86400,
            self::WEEKLY => 86400 * 7,
            self::MONTHLY => 86400 * 30,
        };
    }
}
