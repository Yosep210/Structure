<?php

namespace App\Modules\Withdraw\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum TypeWithdraw: string
{
    use HasEnumOptions;

    case Bonus = 'bonus';
    case Reward = 'reward';
    case Shop = 'shop';
    case Shop_Refund = 'shop_refund';
    case Transfer = 'transfer';
    case Withdraw = 'withdraw';

    public function label(): string
    {
        return match ($this) {
            self::Bonus => 'Bonus',
            self::Reward => 'Reward',
            self::Shop => 'Shop',
            self::Shop_Refund => 'Shop Refund',
            self::Transfer => 'Transfer',
            self::Withdraw => 'Withdraw',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Bonus => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-emerald-700 bg-emerald-50/50 dark:bg-emerald-950/20 dark:text-emerald-400 ring-emerald-600/20',
            self::Reward => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-amber-700 bg-amber-50/50 dark:bg-amber-950/20 dark:text-amber-400 ring-amber-600/20',
            self::Shop => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-indigo-700 bg-indigo-50/50 dark:bg-indigo-950/20 dark:text-indigo-400 ring-indigo-600/20',
            self::Shop_Refund => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-cyan-700 bg-cyan-50/50 dark:bg-cyan-950/20 dark:text-cyan-400 ring-cyan-600/20',
            self::Transfer => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-purple-700 bg-purple-50/50 dark:bg-purple-950/20 dark:text-purple-400 ring-purple-600/20',
            self::Withdraw => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-rose-700 bg-rose-50/50 dark:bg-rose-950/20 dark:text-rose-400 ring-rose-600/20',
        };
    }
}
