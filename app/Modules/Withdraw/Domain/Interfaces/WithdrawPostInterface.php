<?php

namespace App\Modules\Withdraw\Domain\Interfaces;

interface WithdrawPostInterface
{
    public function confirm(int $id, string $adminUsername): bool;
}
