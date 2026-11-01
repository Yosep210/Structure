<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum RegType: string
{
    use HasEnumOptions;

    case ADMIN = 'admin';
    case PRODUK = 'pin';
    case WALLET = 'wallet';
    case TRANSFER = 'transfer';
    case REFERRAL = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::PRODUK => 'PRODUK MEMBER',
            self::WALLET => 'WALLET MEMBER',
            self::TRANSFER => 'TRANSFER',
            self::REFERRAL => 'REFERRAL',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 dark:text-red-400',
            self::PRODUK => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
            self::WALLET => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-yellow-700 dark:text-yellow-400',
            self::TRANSFER => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 dark:text-blue-400',
            self::REFERRAL => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-amber-700 dark:text-amber-400',
        };
    }
}
