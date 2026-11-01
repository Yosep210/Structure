<?php

namespace App\Modules\Member\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetSponsorListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(Request $request): array
    {
        $sortColumns = [
            'username' => 'members.username',
            'name' => 'members.name',
            'rank_id' => 'mn.rank_id',
            'total_sponsored' => 'total_sponsored',
            'total_active' => 'total_active',
            'total_non_active' => 'total_non_active',
            'status' => 'members.status',
            'created_at' => 'members.created_at',
        ];

        $query = ModelMember::onlyMembers()
            ->leftJoin('member_networks as mn', function ($q) {
                $q->on('mn.member_id', '=', 'members.id')
                    ->where('mn.hu_index', '=', 1);
            })
            ->select([
                'members.*',
                'mn.id as mn_id',
                'mn.rank_id as rank_id',
            ])
            ->selectRaw(
                '(SELECT COUNT(*) FROM member_networks WHERE sponsor_id = mn.id) as total_sponsored'
            )
            ->selectRaw(
                '(SELECT COUNT(*) FROM member_networks JOIN members as m ON m.id = member_networks.member_id WHERE member_networks.sponsor_id = mn.id AND m.status = 1) as total_active'
            )
            ->selectRaw(
                '(SELECT COUNT(*) FROM member_networks JOIN members as m ON m.id = member_networks.member_id WHERE member_networks.sponsor_id = mn.id AND m.status = 0) as total_non_active'
            )
            ->groupBy('members.id', 'mn.id');

        $query = $this->filters($query, $request, [
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
            ],
            'select' => [
                'rank_id' => 'mn.rank_id',
                'status' => 'members.status',
            ],
            'total_range' => [
                'total_active' => 'total_active',
                'total_non_active' => 'total_non_active',
            ],
            'date' => [
                'created_at' => 'members.created_at',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort($query, $request, $sortColumns, 'total_sponsored', 'desc');
        $sponsor = $this->paginate($query, $request);

        return [
            'sponsor' => $sponsor,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
