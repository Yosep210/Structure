<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum RankMember: int
{
    use HasEnumOptions;

    case MEMBER = 1;
    case STAR = 2;

    public function label(): string
    {
        return match ($this) {
            self::MEMBER => 'Member',
            self::STAR => 'Star Member',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MEMBER => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 dark:text-blue-400',
            self::STAR => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
        };
    }
}
