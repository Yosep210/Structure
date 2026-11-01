<?php

namespace App\Modules\Withdraw\Domain\Interfaces;

use App\Modules\Withdraw\Infrastructure\Eloquent\ModelWithdraw;

interface WithdrawGetInterface
{
    public function getByWdId(int $id, bool $lockForUpdate = false): ?ModelWithdraw;

    public function getWithdrawTotals(?string $username = null): array;
}
