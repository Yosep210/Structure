<?php

namespace App\Modules\Member\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetMemberListQuery
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
            'rank_id' => 'network.rank_id',
            'contact' => 'members.email',
            'email' => 'members.email',
            'phone' => 'profile.phone',
            'sponsor' => 'sponsor_member.username',
            'parent' => 'parent_member.username',
            'position' => 'network.position',
            'status' => 'members.status',
            'created_at' => 'members.created_at',
            'last_login' => 'members.last_login',
        ];

        $query = ModelMember::onlyMembers()
            ->join('member_networks as network', function ($query) {
                $query->on('network.member_id', '=', 'members.id')
                    ->where('network.hu_index', '=', 1);
            })
            ->leftJoin('member_profiles as profile', 'profile.member_id', '=', 'members.id')
            ->leftJoin('member_networks as sponsor_net', 'sponsor_net.id', '=', 'network.sponsor_id')
            ->leftJoin('members as sponsor_member', 'sponsor_member.id', '=', 'sponsor_net.member_id')
            ->leftJoin('member_networks as parent_net', 'parent_net.id', '=', 'network.parent_id')
            ->leftJoin('members as parent_member', 'parent_member.id', '=', 'parent_net.member_id')
            ->select([
                'members.id',
                'members.username',
                'members.name',
                'members.email',
                'members.status',
                'network.position',
                'network.rank_id',
                'profile.phone',
                'sponsor_member.username as sponsor_username',
                'sponsor_member.name as sponsor_name',
                'parent_member.username as parent_username',
                'parent_member.name as parent_name',
                'members.created_at',
                'members.last_login',
            ]);

        $query = $this->filters($query, $request, [
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
            ],
            'select' => [
                'rank_id' => 'network.rank_id',
                'position' => 'network.position',
                'status' => 'members.status',
            ],
            'multi' => [
                'contact' => ['members.email', 'profile.phone'],
                'sponsor' => ['sponsor_member.username', 'sponsor_member.name'],
                'parent' => ['parent_member.username', 'parent_member.name'],
            ],
            'date' => [
                'created_at' => 'members.created_at',
                'last_login' => 'members.last_login',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort($query, $request, $sortColumns, 'created_at', 'desc');
        $member = $this->paginate($query, $request);

        return [
            'member' => $member,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
