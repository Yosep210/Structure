<?php

namespace App\Modules\EWallet\Infrastructure\Repositories;

use App\Modules\EWallet\Domain\Interfaces\EWalletGetInterface;
use App\Modules\EWallet\Domain\Interfaces\EWalletPostInterface;
use App\Modules\EWallet\Infrastructure\Eloquent\ModelEWallet;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Withdraw\Domain\Enums\WithdrawCategory;
use Illuminate\Support\Collection;

class EWalletRepository implements EWalletGetInterface, EWalletPostInterface
{
    public function __construct(
        protected ModelEWallet $model
    ) {}

    public function getAllEwalletEntry(): ?ModelEWallet
    {
        return ModelEWallet::query()
            ->where('category', WithdrawCategory::COMMISSION->value)
            ->where('status', GeneralStatus::ACTIVE->value)
            ->selectRaw("
                    SUM(CASE WHEN type = 'IN' THEN amount ELSE 0 END) as total_in,
                    SUM(CASE WHEN type = 'OUT' THEN amount ELSE 0 END) as total_out
                ")
            ->first();
    }

    public function getAllByWdStatus(): Collection
    {
        return ModelEWallet::query()
            ->join('members', 'ewallets.member_id', '=', 'members.id')
            ->leftJoin('member_profiles', 'members.id', '=', 'member_profiles.member_id')
            ->where('category', WithdrawCategory::COMMISSION->value)
            ->where('ewallets.status', GeneralStatus::ACTIVE->value)
            ->selectRaw("
                    COALESCE(member_profiles.wd_status, 0) as wd_status,
                    SUM(CASE WHEN ewallets.type = 'IN' THEN ewallets.amount ELSE 0 END) -
                    SUM(CASE WHEN ewallets.type = 'OUT' THEN ewallets.amount ELSE 0 END) as balance
                ")
            ->groupBy('wd_status')
            ->get();
    }

    public function getEwalletMemberEntry($id): ?ModelEWallet
    {
        return ModelEWallet::where('member_id', $id)
            ->where('status', GeneralStatus::ACTIVE->value)
            ->selectRaw("
                    SUM(CASE WHEN type = 'IN' THEN amount ELSE 0 END) as total_in,
                    SUM(CASE WHEN type = 'OUT' THEN amount ELSE 0 END) as total_out
                ")
            ->first();
    }
}
