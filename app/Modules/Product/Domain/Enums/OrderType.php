<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum OrderType: string
{
    use HasEnumOptions;

    case REGISTER = 'register';
    case SHOP = 'shop';
    case RO = 'ro';
    case UPGRADE = 'upgrade';
    case GENERATE = 'generate';
    case MEMBER = 'member';
    case MANUAL_RO = 'manual_ro';
    case STOCKIST = 'stockist';

    public function label(): string
    {
        return match ($this) {
            self::REGISTER => 'Register',
            self::SHOP => 'Shop',
            self::RO => 'Repeat Order',
            self::UPGRADE => 'Upgrade',
            self::GENERATE => 'Generate',
            self::MEMBER => 'Member',
            self::MANUAL_RO => 'Manual RO',
            self::STOCKIST => 'Stockist',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REGISTER => 'Register',
            self::SHOP => 'Shop',
            self::RO => 'Repeat Order',
            self::UPGRADE => 'Upgrade',
            self::GENERATE => 'Generate',
            self::MEMBER => 'Member',
            self::MANUAL_RO => 'Manual RO',
            self::STOCKIST => 'Stockist',
        };
    }
}
