<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum PositionMember: string
{
    use HasEnumOptions;

    case LEFT = 'left';
    case RIGHT = 'right';

    public function label(): string
    {
        return match ($this) {
            self::LEFT => 'Kiri',
            self::RIGHT => 'Kanan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LEFT => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
            self::RIGHT => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-yellow-700 dark:text-yellow-400',
        };
    }
}
