<?php

namespace App\Modules\Withdraw\Infrastructure\Repositories;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Withdraw\Domain\Enums\WithdrawStatus;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawGetInterface;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawPostInterface;
use App\Modules\Withdraw\Infrastructure\Eloquent\ModelWithdraw;
use Illuminate\Database\Eloquent\Builder;
use Override;

class WithdrawRepository implements WithdrawGetInterface, WithdrawPostInterface
{
    public function __construct(
        protected ModelWithdraw $model
    ) {}

    public function getByWdId(int $id, bool $lockForUpdate = false): ?ModelWithdraw
    {
        return $this->model::query()
            ->with('member')
            ->where('id', $id)
            ->when($lockForUpdate, fn (Builder $q) => $q->lockForUpdate())
            ->first();
    }

    /**
     * @return array{totalWd: float, totalReceipt: float, totalTransfer: float}
     */
    public function getWithdrawTotals(?string $username = null): array
    {
        $memberId = ModelMember::where('username', $username)->pluck('id')->first();

        $totals = ModelWithdraw::query()
            ->when($username !== null, fn (Builder $q) => $q->where('member_id', $memberId))
            ->selectRaw('
                COALESCE(SUM(nominal), 0) as total_wd,
                COALESCE(SUM(nominal_receipt), 0) as total_receipt,
                COALESCE(SUM(CASE WHEN status = ? THEN nominal_receipt ELSE 0 END), 0) as total_transfer
            ', [WithdrawStatus::TRASFERED->value])
            ->first();

        return [
            'totalWd' => (float) ($totals->total_wd ?? 0),
            'totalReceipt' => (float) ($totals->total_receipt ?? 0),
            'totalTransfer' => (float) ($totals->total_transfer ?? 0),
        ];
    }

    #[Override]
    public function confirm(int $id, string $adminUsername): bool
    {
        return (bool) ModelWithdraw::where('id', $id)->update([
            'status' => WithdrawStatus::TRASFERED,
            'dateconfirmed' => now(),
            'confirm_by' => $adminUsername,
        ]);
    }
}
