<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum PinType: string
{
    use HasEnumOptions;

    case REGISTER = 'perdana';
    case RO = 'ro';

    public function label(): string
    {
        return match ($this) {
            self::REGISTER => 'Register',
            self::RO => 'RO',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REGISTER => 'blue',
            self::RO => 'emerald',
        };
    }
}
