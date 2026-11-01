<?php

namespace App\Modules\Member\Domain\Enums;

enum GeneralStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'red',
        };
    }
}
