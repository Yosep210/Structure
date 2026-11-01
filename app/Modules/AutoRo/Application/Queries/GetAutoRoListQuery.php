<?php

namespace App\Modules\AutoRo\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetAutoRoListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null): array
    {
        $sortColumns = [
            'username' => 'members.username',
            'name' => 'members.name',
            'bonus_total' => 'bonus_total',
        ];

        // =========================================================================
        // [1. QUERY BASE]: Rekap Akumulasi Saldo Auto RO per Member (IN - OUT)
        // =========================================================================
        $query = ModelMember::onlyMembers()
            ->join('member_networks as network', 'members.id', '=', 'network.member_id')
            ->join('auto_ros as ro', function ($join) {
                $join->on('network.id', '=', 'ro.member_network_id')
                    ->where('ro.status', '=', GeneralStatus::ACTIVE->value);
            })
            ->select([
                'members.id',
                'members.username',
                'members.name',
            ])
            ->selectRaw("COALESCE(SUM(CASE WHEN ro.type = 'IN' THEN ro.amount ELSE -ro.amount END), 0) as bonus_total")
            ->groupBy('members.id');

        $query = $this->filters($query, $request, [
            'text' => [
                'name' => 'members.name',
                'username' => 'members.username',
            ],
            'total_range' => [
                'bonus_total' => 'bonus_total',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: 'bonus_total',
            defaultDirection: 'desc',
            tieBreaker: ['members.id' => 'asc']
        );
        $autoRo = $this->paginate($query, $request);

        return [
            'autoRo' => $autoRo,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
