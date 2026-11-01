<?php

namespace App\Modules\Bonus\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum BonusType: int
{
    use HasEnumOptions;

    case BONUS_SPONSOR = 1;
    case BONUS_PAIRING = 2;
    case BONUS_LEVEL = 3;
    case BONUS_GENERATION = 4;
    case BONUS_GEN_RO = 5;
    // case BONUS_STOCKIST = 6;

    public function label(): string
    {
        return match ($this) {
            self::BONUS_SPONSOR => 'Bonus Sponsor',
            self::BONUS_PAIRING => 'Bonus Pasangan',
            self::BONUS_LEVEL => 'Bonus Level',
            self::BONUS_GENERATION => 'Bonus Generasi',
            self::BONUS_GEN_RO => 'Bonus Generasi RO',
            // self::BONUS_STOCKIST => 'Bonus Stokis',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BONUS_SPONSOR => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400',
            self::BONUS_PAIRING => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-purple-700 bg-purple-50 dark:bg-purple-950/30 dark:text-purple-400',
            self::BONUS_LEVEL => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400',
            self::BONUS_GENERATION => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-amber-700 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400',
            self::BONUS_GEN_RO => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-teal-700 bg-teal-50 dark:bg-teal-950/30 dark:text-teal-400',
            // self::BONUS_STOCKIST => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-rose-700 bg-rose-50 dark:bg-rose-950/30 dark:text-rose-400',
        };
    }
}
