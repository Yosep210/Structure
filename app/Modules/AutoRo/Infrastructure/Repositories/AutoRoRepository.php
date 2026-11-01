<?php

namespace App\Modules\AutoRo\Infrastructure\Repositories;

use App\Modules\AutoRo\Domain\Interfaces\AutoRoGetInterface;
use App\Modules\AutoRo\Domain\Interfaces\AutoRoPostInterface;
use App\Modules\AutoRo\Infrastructure\Eloquent\ModelAutoRo;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;

class AutoRoRepository implements AutoRoGetInterface, AutoRoPostInterface
{
    public function __construct(
        protected ModelAutoRo $model
    ) {}

    /**
     * @return array{totalRo: float, totalRoMonth: float}
     */
    public function getRoHistory(string $username): array
    {
        $member = ModelMember::where('username', $username)->with('network')->first();
        $networkId = $member?->network?->id;

        if (! $networkId) {
            return [
                'totalRo' => 0.0,
                'totalRoMonth' => 0.0,
            ];
        }

        $totalRo = (float) ModelAutoRo::query()
            ->where('member_network_id', $networkId)
            ->where('status', GeneralStatus::ACTIVE->value)
            ->sum('amount');

        $totalRoMonth = (float) ModelAutoRo::query()
            ->where('member_network_id', $networkId)
            ->where('status', GeneralStatus::ACTIVE->value)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            'totalRo' => $totalRo,
            'totalRoMonth' => $totalRoMonth,
        ];
    }

    /**
     * @return array{autoRoBalance: float, autoRoIn: float, autoRoOut: float}
     */
    public function getRoEntry(string $username): array
    {
        $member = ModelMember::where('username', $username)->with('network')->first();
        $network = $member?->network;
        $networkId = $network?->id;
        $autoRoBalance = (float) ($network?->autoro_balance ?? 0);

        if (! $networkId) {
            return [
                'autoRoBalance' => 0.0,
                'autoRoIn' => 0.0,
                'autoRoOut' => 0.0,
            ];
        }

        $autoRoTotals = ModelAutoRo::query()
            ->where('member_network_id', $networkId)
            ->where('status', GeneralStatus::ACTIVE->value)
            ->selectRaw("
                SUM(CASE WHEN type = 'IN' THEN amount ELSE 0 END) as total_in,
                SUM(CASE WHEN type = 'OUT' THEN amount ELSE 0 END) as total_out
            ")
            ->first();

        $autoRoIn = (float) ($autoRoTotals->total_in ?? 0);
        $autoRoOut = (float) ($autoRoTotals->total_out ?? 0);

        return [
            'autoRoBalance' => $autoRoBalance,
            'autoRoIn' => $autoRoIn,
            'autoRoOut' => $autoRoOut,
        ];
    }
}
