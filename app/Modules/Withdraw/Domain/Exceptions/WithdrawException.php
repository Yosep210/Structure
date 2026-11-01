<?php

namespace App\Modules\Withdraw\Domain\Exceptions;

use App\Exceptions\MessageException;

class WithdrawException extends MessageException
{
    public static function withdrawAlreadyProses(): self
    {
        return static::error('Transaksi withdraw sudah pernah diproses sebelumnya.');
    }

    public static function wdConfirmed(string $username, object $withdraw): self
    {
        $nominal = number_format((float) ($withdraw->nominal_receipt ?? 0), 0, ',', '.');

        return static::success("Withdraw member ({$username}) sebesar Rp {$nominal} berhasil dikonfirmasi.");
    }
}
