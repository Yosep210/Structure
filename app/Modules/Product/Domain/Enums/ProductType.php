<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum ProductType: string
{
    use HasEnumOptions;

    case ALL = 'all';
    case PERDANA = 'perdana';
    case RO = 'ro';

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'All Posting',
            self::PERDANA => 'Register',
            self::RO => 'RO',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ALL => 'blue',
            self::PERDANA => 'green',
            self::RO => 'yellow',
        };
    }
}
