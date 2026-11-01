<?php

namespace App\Enums;

use App\Concerns\HasEnumOptions;

enum MemberRole: string
{
    use HasEnumOptions;

    case ADMIN = 'admin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MEMBER => 'Member',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-red-700 dark:text-red-400',
            self::MEMBER => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-green-700 dark:text-green-400',
        };
    }
}
