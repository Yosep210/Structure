<?php

namespace App\Modules\AutoRo\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\AutoRo\Infrastructure\Eloquent\ModelAutoRo;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GetAutoRoMonthlyQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, array $auth = []): array
    {
        $isAdmin = $auth['isAdmin'] ?? false;
        $member = $auth['member'] ?? null;

        $sortColumns = [
            'month_ro' => 'monthly_ro.month_ro',
            'username' => 'monthly_ro.username',
            'name' => 'monthly_ro.name',
            'total_ro' => 'monthly_ro.total_ro',
        ];

        $driver = ModelAutoRo::query()->getConnection()->getDriverName();
        $dateFormatExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-01', auto_ros.created_at)"
            : 'DATE_FORMAT(auto_ros.created_at, "%Y-%m-01")';

        // =========================================================================
        // [1. DROPDOWN BULAN]: Ambil daftar bulan unik yang memiliki riwayat Auto RO
        // =========================================================================
        $monthsQuery = ModelAutoRo::query()
            ->where('auto_ros.status', GeneralStatus::ACTIVE->value);

        if (! $isAdmin && $member) {
            $monthsQuery->join('member_networks as network', 'auto_ros.member_network_id', '=', 'network.id')
                ->where('network.member_id', $member->id);
        }

        $months = $monthsQuery
            ->selectRaw("DISTINCT {$dateFormatExpr} as value")
            ->orderByDesc('value')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => (string) $item->value,
                    'label' => Carbon::parse($item->value)->translatedFormat('F Y'),
                ];
            })
            ->values()
            ->all();

        if (empty($months)) {
            $months = [
                [
                    'value' => now()->format('Y-m-01'),
                    'label' => now()->translatedFormat('F Y'),
                ],
            ];
        }

        // =========================================================================
        // [2. SUBQUERY]: Agregasi Total Auto RO per Member per Bulan
        // =========================================================================
        $subQuery = ModelAutoRo::query()
            ->join('member_networks as network', 'auto_ros.member_network_id', '=', 'network.id')
            ->join('members as member', 'network.member_id', '=', 'member.id')
            ->where('auto_ros.status', GeneralStatus::ACTIVE->value)
            ->when(! $isAdmin && $member, function ($q) use ($member) {
                $q->where('member.id', $member->id);
            })
            ->select([
                'member.id',
                'member.name',
                'member.username',
            ])
            ->selectRaw("{$dateFormatExpr} as month_ro")
            ->selectRaw('COALESCE(SUM(auto_ros.amount), 0) as total_ro')
            ->groupBy('member.id', 'member.name', 'member.username', 'month_ro');

        $monthVal = $request?->input('month_ro');

        if ($request?->filled('month_ro')) {
            $subQuery->whereRaw("{$dateFormatExpr} = ?", [$monthVal]);
        } elseif ($isAdmin && ! $request?->filled('username')) {
            $latestMutation = ModelAutoRo::query()
                ->where('auto_ros.status', GeneralStatus::ACTIVE->value)
                ->latest('auto_ros.created_at')
                ->first();

            if ($latestMutation && $latestMutation->created_at) {
                $createdDate = Carbon::parse($latestMutation->created_at);
                $subQuery->whereYear('auto_ros.created_at', $createdDate->year)
                    ->whereMonth('auto_ros.created_at', $createdDate->month);
            }
        }

        // =========================================================================
        // [3. FILTER & QUERY UTAMA]: Filter teks, rentang nominal, dan pagination
        // =========================================================================
        $finalQuery = ModelMember::query()
            ->fromSub($subQuery, 'monthly_ro')
            ->select([
                'monthly_ro.id',
                'monthly_ro.name',
                'monthly_ro.username',
                'monthly_ro.month_ro',
                'monthly_ro.total_ro',
            ]);

        $query = $this->filters($finalQuery, $request, [
            'text' => [
                'name' => 'monthly_ro.name',
                'username' => 'monthly_ro.username',
            ],
            'nominal_range' => [
                'total_ro' => 'monthly_ro.total_ro',
            ],
        ]);

        $defaultSort = [
            'month_ro' => 'desc',
            'total_ro' => 'desc',
            'monthly_ro.username' => 'asc',
        ];

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: $defaultSort,
            defaultDirection: 'desc',
            tieBreaker: 'monthly_ro.username'
        );

        $monthlies = $this->paginate($query, $request);

        return [
            'monthlies' => $monthlies,
            'months' => $months,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
