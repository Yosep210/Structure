<?php

namespace App\Modules\Withdraw\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum WithdrawCategory: string
{
    use HasEnumOptions;

    case COMMISSION = 'commission';
    case PROFIT = 'profit';

    public function label(): string
    {
        return match ($this) {
            self::COMMISSION => 'Commission',
            self::PROFIT => 'Profit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMMISSION => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50/50 dark:bg-green-950/20 dark:text-green-400 ring-green-600/20',
            self::PROFIT => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 bg-blue-50/50 dark:bg-blue-950/20 dark:text-blue-400 ring-blue-600/20',
        };
    }
}
