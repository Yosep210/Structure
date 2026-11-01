<?php

namespace App\Modules\Withdraw\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum WithdrawStatus: int
{
    use HasEnumOptions;

    case PENDING = 0;
    case TRASFERED = 1;
    case PROCESSED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::TRASFERED => 'Disetujui',
            self::PROCESSED => 'Sedang Diproses',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-yellow-700 bg-yellow-50/50 dark:bg-yellow-950/20 dark:text-yellow-400 ring-yellow-600/20',
            self::TRASFERED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50/50 dark:bg-green-950/20 dark:text-green-400 ring-green-600/20',
            self::PROCESSED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 bg-blue-50/50 dark:bg-blue-950/20 dark:text-blue-400 ring-blue-600/20',
        };
    }
}
