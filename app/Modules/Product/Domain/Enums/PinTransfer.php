<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum PinTransfer: string
{
    use HasEnumOptions;

    case TRANSFER = 'transfer_pin';
    case ORDER = 'order_pin';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER => 'Transfer',
            self::ORDER => 'Order',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TRANSFER => 'green',
            self::ORDER => 'blue',
        };
    }
}
