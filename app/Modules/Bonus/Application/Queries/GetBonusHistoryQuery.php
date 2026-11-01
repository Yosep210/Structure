<?php

namespace App\Modules\Bonus\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Bonus\Infrastructure\Eloquent\ModelBonus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetBonusHistoryQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?string $username = null): array
    {
        $sortColumns = [
            'created_at' => 'bonus.created_at',
            'id' => 'bonus.id',
            'username' => 'members.username',
            'name' => 'members.name',
            'amount' => 'bonus.amount',
            'type' => 'bonus.type',
            'description' => 'bonus.description',
        ];

        // =========================================================================
        // [1. QUERY BASE]: Relasi Bonus -> Member Network -> Member
        // =========================================================================
        $query = ModelBonus::query()
            ->from('bonuses as bonus')
            ->join('member_networks as network', 'network.id', '=', 'bonus.member_network_id')
            ->join('members', 'members.id', '=', 'network.member_id')
            ->select([
                'bonus.id',
                'bonus.created_at',
                'bonus.type',
                'bonus.description',
                'bonus.amount',
                'bonus.tax',
                'bonus.amount_tax',
                'members.username',
                'members.name',
            ])
            // [MODE MEMBER / ADMIN AUDIT]: Filter spesifik username jika parameter terisi
            ->when($username !== null, function (Builder $query) use ($username) {
                $query->where('members.username', $username);
            });

        $query = $this->filters($query, $request, [
            'date' => [
                'created_at' => 'bonus.created_at',
            ],
            'nominal_range' => [
                'amount' => 'bonus.amount',
            ],
            'select' => [
                'type' => 'bonus.type',
            ],
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
                'description' => 'bonus.description',
            ],
        ]);

        $defaultSort = [
            'created_at' => 'desc',
            'id' => 'desc',
        ];

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: $defaultSort,
            defaultDirection: 'desc',
            tieBreaker: 'bonus.id'
        );

        $bonus = $this->paginate($query, $request);

        return [
            'bonus' => $bonus,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
