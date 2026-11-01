<?php

namespace App\Modules\Member\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetGenerationListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(Request $request, ModelMember $targetMember, ModelNetwork $targetNode, bool $isAdmin = false, ?string $username = null): array
    {
        $sortColumns = [
            'member_username' => 'members.username',
            'member_name' => 'members.name',
            'sponsor_username' => 'sponsor_member.username',
            'sponsor_name' => 'sponsor_member.name',
            'gen' => 'mn.gen',
            'group' => 'group_level',
            'total_omzet' => 'mn.total_omzet',
        ];

        // =========================================================================
        // [1. PENENTUAN MODE & DEFAULT SORT]
        // Member / Detail Mode: Urutkan gen asc, group asc, username asc
        // Admin Global View: Default gen asc
        // =========================================================================
        $isMemberView = ! $isAdmin || $username !== null;
        $defaultSort = $isMemberView
            ? [
                'gen' => 'asc',
                'group' => 'asc',
                'member_username' => 'asc',
            ]
            : 'gen';

        $targetMemberId = $targetMember->id;
        $targetNodeGen = $targetNode->gen;
        $treePrefix = $targetNode->tree_sponsor.'-%';

        // =========================================================================
        // [2. QUERY DOWNLINE GENERASI SPONSORSHIP DENGAN RECURSIVE HIERARCHY]
        // =========================================================================
        $query = ModelMember::onlyMembers()
            ->join('member_networks as mn', function ($query) {
                $query->on('mn.member_id', '=', 'members.id')
                    ->where('mn.hu_index', '=', 1);
            })
            ->leftJoin('member_networks as sponsor_net', 'sponsor_net.id', '=', 'mn.sponsor_id')
            ->leftJoin('members as sponsor_member', 'sponsor_member.id', '=', 'sponsor_net.member_id')
            ->select([
                'members.id',
                'members.username as member_username',
                'members.name as member_name',
                'mn.gen',
                'mn.total_omzet',
                'sponsor_member.username as sponsor_username',
                'sponsor_member.name as sponsor_name',
            ])
            ->where('mn.tree_sponsor', 'like', $treePrefix)
            ->where('mn.gen', '>', $targetNodeGen)
            ->selectRaw('(mn.gen - ?) as gen_level', [$targetNodeGen])
            ->selectRaw('
                (
                    WITH RECURSIVE Hierarchy AS (
                        SELECT member_id, sponsor_id, `group`
                        FROM member_networks
                        WHERE member_id = members.id AND hu_index = 1
                        UNION ALL
                        SELECT mn_parent.member_id, mn_parent.sponsor_id, mn_parent.`group`
                        FROM member_networks mn_parent
                        INNER JOIN Hierarchy h ON h.sponsor_id = mn_parent.member_id
                        WHERE mn_parent.hu_index = 1
                    )
                    SELECT `group` FROM Hierarchy WHERE sponsor_id = ? LIMIT 1
                ) as `group_level`
            ', [$targetMemberId])
            ->groupBy('members.id', 'mn.id');
        // ->when($request->filled('gen_min'), function (Builder $query) use ($request, $targetNodeGen) {
        //     if (! $request->filled('gen_max')) {
        //         $query->whereRaw(
        //             'CAST((mn.gen - ?) AS CHAR) LIKE ?',
        //             [$targetNodeGen, (string) $request->input('gen_min') . '%'],
        //         );

        //         return;
        //     }

        //     $query->where('mn.gen', '>=', (int) $request->input('gen_min') + $targetNodeGen);
        // })
        // ->when($request->filled('gen_max'), function (Builder $query) use ($request, $targetNodeGen) {
        //     $query->where('mn.gen', '<=', (int) $request->input('gen_max') + $targetNodeGen);
        // })
        // ->when($request->filled('group_min'), function (Builder $query) use ($request) {
        //     $query->having('group', '>=', (int) $request->input('group_min'));
        // })
        // ->when($request->filled('group_max'), function (Builder $query) use ($request) {
        //     $query->having('group', '<=', (int) $request->input('group_max'));
        // });

        $query = $this->filters(
            $query,
            $request,
            [
                'multi' => [
                    'member_username' => ['members.username', 'members.name'],
                    'sponsor_username' => ['sponsor_member.username', 'sponsor_member.name'],
                ],
                'total_range' => [
                    'gen' => 'gen_level',
                    'group' => 'group_level',
                ],
                'nominal_range' => [
                    'total_omzet' => 'mn.total_omzet',
                ],
            ]
        );

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: $defaultSort,
            defaultDirection: 'asc',
            tieBreaker: $isMemberView ? null : 'members.username'
        );
        $generation = $this->paginate($query, $request);

        return [
            'generation' => $generation,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
