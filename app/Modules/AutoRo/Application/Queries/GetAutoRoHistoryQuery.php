<?php

namespace App\Modules\AutoRo\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetAutoRoHistoryQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?string $username = null): array
    {
        // =========================================================================
        // [1. SORT COLUMNS & MAPPING]: Pemetaan Kolom Pengurutan
        // =========================================================================
        $sortColumns = [
            'created_at' => 'ro.created_at',
            'username' => 'members.username',
            'name' => 'members.name',
            'type' => 'ro.type',
            'nominal' => 'ro.nominal',
            'amount' => 'ro.amount',
            'description' => 'ro.description',
        ];

        // =========================================================================
        // [2. QUERY BASE]: Riwayat Mutasi Auto RO Member
        // =========================================================================
        $query = ModelMember::onlyMembers()
            ->join('member_networks as network', 'members.id', '=', 'network.member_id')
            ->join('auto_ros as ro', function ($join) {
                $join->on('network.id', '=', 'ro.member_network_id')
                    ->where('ro.status', '=', GeneralStatus::ACTIVE->value);
            })
            ->when($username !== null, function ($q) use ($username) {
                $q->where('members.username', $username);
            })
            ->select([
                'ro.id',
                'ro.created_at',
                'ro.type',
                'ro.nominal',
                'ro.amount',
                'ro.description',
                'members.username',
                'members.name',
            ]);

        // =========================================================================
        // [3. FILTER QUERY]: Filter Tanggal, Teks, Nominal, dan Tipe
        // =========================================================================
        $query = $this->filters($query, $request, [
            'date' => [
                'created_at' => 'ro.created_at',
            ],
            'text' => [
                'name' => 'members.name',
                'username' => 'members.username',
                'description' => 'ro.description',
            ],
            'nominal_range' => [
                'nominal' => 'ro.nominal',
                'amount' => 'ro.amount',
            ],
            'select' => [
                'type' => 'ro.type',
            ],
        ]);

        // =========================================================================
        // [4. SORTING & PAGINATION]: Pengurutan Default & Paginasi
        // =========================================================================
        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            'created_at',
            'desc',
            'members.username'
        );
        $histories = $this->paginate($query, $request);

        return [
            'histories' => $histories,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
