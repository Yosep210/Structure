<?php

namespace App\Modules\EWallet\Domain\Interfaces;

use App\Modules\EWallet\Infrastructure\Eloquent\ModelEWallet;
use Illuminate\Support\Collection;

interface EWalletGetInterface
{
    public function getAllEwalletEntry(): ?ModelEWallet;

    public function getAllByWdStatus(): Collection;

    public function getEwalletMemberEntry($id): ?ModelEWallet;
}
