<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum PinStatus: int
{
    use HasEnumOptions;

    case AVAILABLE = 0;
    case RESERVED = 1;
    case USED = 2;

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::RESERVED => 'Reserved',
            self::USED => 'Used',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'green',
            self::RESERVED => 'yellow',
            self::USED => 'red',
        };
    }
}
