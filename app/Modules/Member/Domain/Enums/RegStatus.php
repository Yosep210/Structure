<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum RegStatus: int
{
    use HasEnumOptions;

    case PENDING = 0;
    case CONFIRMED = 1;
    case CANCELED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELED => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-yellow-700 dark:text-yellow-400',
            self::CONFIRMED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
            self::CANCELED => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 dark:text-red-400',
        };
    }
}
