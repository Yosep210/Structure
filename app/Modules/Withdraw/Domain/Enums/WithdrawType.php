<?php

namespace App\Modules\Withdraw\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum WithdrawType: string
{
    use HasEnumOptions;

    case BANK_TRANSFER = 'bank_transfer';
    case EWALLET = 'ewallet';
    case MOBILE_WALLET = 'mobile_wallet';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::EWALLET => 'e-Wallet',
            self::MOBILE_WALLET => 'Mobile Wallet',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 bg-blue-50/50 dark:bg-blue-950/20 dark:text-blue-400 ring-blue-600/20',
            self::EWALLET => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50/50 dark:bg-green-950/20 dark:text-green-400 ring-green-600/20',
            self::MOBILE_WALLET => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-purple-700 bg-purple-50/50 dark:bg-purple-950/20 dark:text-purple-400 ring-purple-600/20',
        };
    }
}
