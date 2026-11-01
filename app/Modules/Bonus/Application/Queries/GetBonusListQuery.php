<?php

namespace App\Modules\Bonus\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetBonusListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?string $dateFrom = null, ?string $dateTo = null, bool $isStatement = false): array
    {
        $sortColumns = [
            'username' => 'members.username',
            'name' => 'members.name',
            'amount' => 'amount',
        ];

        $dateBindings = array_values(array_filter([$dateFrom, $dateTo], fn ($val) => ! empty($val)));

        // =========================================================================
        // [1. QUERY BASE]: Rekap total akumulasi bonus per member
        // [2. STATEMENT MODE]: Menyaring hanya member dengan total bonus > 0
        // =========================================================================
        $query = ModelMember::onlyMembers()
            ->join('member_networks as network', function ($query) {
                $query->on('network.member_id', '=', 'members.id')
                    ->where('network.hu_index', '=', 1);
            })
            ->select([
                'members.id',
                'members.username',
                'members.name',
            ])->selectRaw(' (
                SELECT COALESCE(SUM(b.amount), 0) 
                FROM bonuses as b 
                WHERE b.member_network_id = network.id
                '.($dateFrom ? ' AND DATE(b.created_at) >= ?' : '').'
                '.($dateTo ? ' AND DATE(b.created_at) <= ?' : '').'
            ) as amount ', $dateBindings)
            ->groupBy('members.id', 'network.id')
            ->when($isStatement, fn ($q) => $q->havingRaw('amount > 0'));

        $query = $this->filters($query, $request, [
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
            ],
            'nominal_range' => [
                'amount' => 'amount',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: 'amount',
            defaultDirection: 'desc',
            tieBreaker: ['members.id' => 'desc']
        );
        $bonus = $this->paginate($query, $request);

        return [
            'bonus' => $bonus,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
