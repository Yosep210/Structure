<?php

namespace App\Modules\Product\Domain\Enums;

use App\Concerns\HasEnumOptions;

enum PaymentMethode: string
{
    use HasEnumOptions;

    case TRANSFER = 'transfer';
    case EWALLET = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER => 'Transfer',
            self::EWALLET => 'Saldo eWallet',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TRANSFER => 'green',
            self::EWALLET => 'blue',
        };
    }
}
