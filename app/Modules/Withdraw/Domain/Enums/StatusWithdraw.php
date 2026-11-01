<?php

namespace App\Modules\Withdraw\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum StatusWithdraw: int
{
    use HasEnumOptions;

    case WD_NON_ACTIVE = 0;
    case WD_MANUAL = 1;
    case WD_AUTO = 2;

    public function label(): string
    {
        return match ($this) {
            self::WD_NON_ACTIVE => 'WITHDRAW TIDAK AKTIF',
            self::WD_MANUAL => 'WITHDRAW MANUAL',
            self::WD_AUTO => 'WITHDRAW OTOMATIS',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WD_NON_ACTIVE => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 bg-red-50/50 dark:bg-red-950/20 dark:text-red-400 ring-red-600/20',
            self::WD_MANUAL => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 bg-blue-50/50 dark:bg-blue-950/20 dark:text-blue-400 ring-blue-600/20',
            self::WD_AUTO => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50/50 dark:bg-green-950/20 dark:text-green-400 ring-green-600/20',
        };
    }
}
