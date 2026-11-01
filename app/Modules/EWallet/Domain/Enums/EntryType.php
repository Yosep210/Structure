<?php

namespace App\Modules\EWallet\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum EntryType: string
{
    use HasEnumOptions;

    case IN = 'IN';
    case OUT = 'OUT';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'IN',
            self::OUT => 'OUT',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50/50 dark:bg-green-950/20 dark:text-green-400 ring-green-600/20',
            self::OUT => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 bg-red-50/50 dark:bg-red-950/20 dark:text-red-400 ring-red-600/20',
        };
    }
}
