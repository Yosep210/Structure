<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum StatusMember: int
{
    use HasEnumOptions;

    case INACTIVE = 0;
    case ACTIVE = 1;
    case BANNED = 2;
    case DELETED = 3;
    case CANCELED = 4;

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Nonaktif',
            self::ACTIVE => 'Aktif',
            self::BANNED => 'Diblokir',
            self::DELETED => 'Dihapus',
            self::CANCELED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INACTIVE => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-zinc-700 dark:text-zinc-400',
            self::ACTIVE => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
            self::BANNED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 dark:text-red-400',
            self::DELETED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-rose-700 dark:text-rose-400',
            self::CANCELED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-yellow-700 dark:text-yellow-400',
        };
    }
}
