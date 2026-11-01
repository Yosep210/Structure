<?php

namespace App\Modules\Bonus\Application\Queries;

use App\Modules\AutoRo\Infrastructure\Eloquent\ModelAutoRo;
use App\Modules\Bonus\Domain\Enums\BonusType;
use App\Modules\Bonus\Infrastructure\Eloquent\ModelBonus;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Withdraw\Domain\Enums\TypeWithdraw;

class GetStatementMemberQuery
{
    public function execute(string $username, string $dateFrom, string $dateTo): array
    {
        $bonuses = ModelBonus::query()
            ->from('bonuses as bonus')
            ->join('member_networks as network', 'network.id', '=', 'bonus.member_network_id')
            ->join('members', 'members.id', '=', 'network.member_id')
            ->where('members.username', $username)
            ->whereDate('bonus.created_at', '>=', $dateFrom)
            ->whereDate('bonus.created_at', '<=', $dateTo)
            ->select(['bonus.type', 'bonus.amount', 'bonus.tax'])
            ->get();

        $items = array_map(function (BonusType $type) use ($bonuses) {
            return [
                'type' => $type->value,
                'label' => $type->label(),
                'color' => $type->color(),
                'amount' => (float) $bonuses->where('type', $type->value)->sum('amount'),
            ];
        }, BonusType::cases());

        $totalBonus = (float) $bonuses->sum('amount');
        $totalTax = (float) $bonuses->sum('tax');

        $totalAutoSave = (float) ModelAutoRo::query()
            ->from('auto_ros as ro')
            ->join('member_networks as network', 'network.id', '=', 'ro.member_network_id')
            ->join('members', 'members.id', '=', 'network.member_id')
            ->where('members.username', $username)
            ->where('ro.status', GeneralStatus::ACTIVE->value)
            ->where('ro.source', TypeWithdraw::Bonus->value)
            ->whereDate('ro.created_at', '>=', $dateFrom)
            ->whereDate('ro.created_at', '<=', $dateTo)
            ->sum('ro.amount');

        $totalEwc = $totalBonus - $totalTax - $totalAutoSave;

        return [
            'items' => $items,
            'summary' => [
                'total_bonus' => $totalBonus,
                'total_tax' => $totalTax,
                'total_autosave' => $totalAutoSave,
                'total_ewc' => $totalEwc,
                'gross' => $totalBonus,
                'tax' => $totalTax,
                'net' => $totalEwc,
            ],
        ];
    }
}
