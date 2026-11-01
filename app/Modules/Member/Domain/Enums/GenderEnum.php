<?php

namespace App\Modules\Member\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum GenderEnum: string
{
    use HasEnumOptions;

    case MALE = 'M';
    case FEMALE = 'F';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MALE => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-blue-700 dark:text-blue-400',
            self::FEMALE => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-pink-700 dark:text-pink-400',
        };
    }
}
