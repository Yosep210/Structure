<?php

namespace App\Modules\Member\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelMemberConfirm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetReportRegQuery
{
    use Filters, Paginates, Sorts;

    /**
     * @return array{report: LengthAwarePaginator, sort: string, direction: string}
     */
    public function execute(Request $request, ?ModelMember $member = null): array
    {
        $sortColumns = [
            'pendaftar' => 'reg_user.username',
            'member' => 'member.username',
            'access' => 'member_confirms.access',
            'bv' => 'member_confirms.bv',
            'total_payment' => 'member_confirms.total_payment',
            'status' => 'member.status',
            'created_at' => 'member_confirms.created_at',
            'dateconfirmed' => 'member_confirms.dateconfirmed',
        ];

        $query = ModelMemberConfirm::query()
            ->leftJoin('members as reg_user', 'member_confirms.member_id', '=', 'reg_user.id')
            ->join('members as member', 'member_confirms.downline_id', '=', 'member.id')
            ->leftJoin('member_profiles as profile', 'member.id', '=', 'profile.member_id')
            ->leftJoin('member_networks as sponsor_net', 'member_confirms.sponsor_id', '=', 'sponsor_net.id')
            ->leftJoin('members as sponsor_member', 'sponsor_net.member_id', '=', 'sponsor_member.id')
            ->leftJoin('shop_orders as shop_order', 'member_confirms.shop_order_id', '=', 'shop_order.id')
            ->whereIn('member.id', ModelMember::onlyMembers()->select('members.id'))
            ->select([
                'member_confirms.*',
                'reg_user.username as pendaftar_username',
                'reg_user.name as pendaftar_name',
                'member.name as member_name',
                'member.username as member_username',
                'member.email as member_email',
                'profile.phone as member_phone',
                'member.status as member_status',
                'sponsor_member.username as sponsor_username',
                'sponsor_member.name as sponsor_name',
            ]);

        if ($member !== null) {
            $networkId = $member->network()->where('hu_index', 1)->value('id');

            $query->where(function ($q) use ($member, $networkId) {
                $q->where('shop_order.created_by', $member->username)
                    ->orWhere('member_confirms.member_id', $member->id)
                    ->orWhere('member_confirms.downline_id', $member->id);

                if ($networkId !== null) {
                    $q->orWhere('member_confirms.sponsor_id', $networkId)
                        ->orWhere('member_confirms.parent_id', $networkId);
                }
            });
        }

        $query = $this->filters($query, $request, [
            'text' => [
                'access' => 'member_confirms.access',
            ],
            'multi' => [
                'pendaftar' => ['reg_user.username', 'reg_user.name'],
                'member' => ['member.username', 'member.name'],
                'contact' => ['member.email', 'profile.phone'],
                'sponsor' => ['sponsor_member.username', 'sponsor_member.name'],
            ],
            'select' => [
                'status' => 'member.status',
            ],
            'date' => [
                'created_at' => 'member_confirms.created_at',
                'dateconfirmed' => 'member_confirms.dateconfirmed',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            ['created_at' => 'desc'],
            tieBreaker: ['id' => 'desc']
        );

        return [
            'report' => $this->paginate($query, $request),
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
