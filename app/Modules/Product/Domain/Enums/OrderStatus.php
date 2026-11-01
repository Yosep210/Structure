<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum OrderStatus: int
{
    use HasEnumOptions;

    case PENDING = 0;
    case PROCESSED = 1;
    case CANCELED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSED => 'Processed',
            self::CANCELED => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSED => 'green',
            self::CANCELED => 'red',
        };
    }
}
