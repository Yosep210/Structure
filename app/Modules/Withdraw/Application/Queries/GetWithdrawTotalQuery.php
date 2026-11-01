<?php

namespace App\Modules\Withdraw\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Withdraw\Infrastructure\Eloquent\ModelWithdraw;
use Illuminate\Http\Request;

class GetWithdrawTotalQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null): array
    {
        $sortColumns = [
            'datewd' => 'withdraw_totals.datewd',
            'trx' => 'withdraw_totals.trx',
            'total_nominal' => 'withdraw_totals.total_nominal',
            'total_receipt' => 'withdraw_totals.total_receipt',
            'total_admin' => 'withdraw_totals.total_admin',
            'total_auto_ro' => 'withdraw_totals.total_auto_ro',
            'total_transferred' => 'withdraw_totals.total_transferred',
        ];

        $driver = ModelWithdraw::query()->getConnection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : 'DATE(created_at)';

        $subQuery = ModelWithdraw::query()
            ->selectRaw("
                    {$dateExpr} as id,
                    {$dateExpr} as datewd,
                    COUNT(id) as trx,
                    COALESCE(SUM(nominal), 0) as total_nominal,
                    COALESCE(SUM(nominal_receipt), 0) as total_receipt,
                    COALESCE(SUM(admin_fund), 0) as total_admin,
                    COALESCE(SUM(auto_ro), 0) as total_auto_ro,
                    COALESCE(SUM(nominal_receipt + admin_fund), 0) as total_transferred
                ")
            ->groupByRaw($dateExpr);

        $query = ModelWithdraw::query()
            ->fromSub($subQuery, 'withdraw_totals')
            ->select([
                'withdraw_totals.id',
                'withdraw_totals.datewd',
                'withdraw_totals.trx',
                'withdraw_totals.total_nominal',
                'withdraw_totals.total_receipt',
                'withdraw_totals.total_admin',
                'withdraw_totals.total_auto_ro',
                'withdraw_totals.total_transferred',
            ]);

        $query = $this->filters($query, $request, [
            'date' => [
                'datewd' => 'withdraw_totals.datewd',
            ],
            'nominal_range' => [
                'trx' => 'withdraw_totals.trx',
                'total_nominal' => 'withdraw_totals.total_nominal',
                'total_receipt' => 'withdraw_totals.total_receipt',
                'total_admin' => 'withdraw_totals.total_admin',
                'total_auto_ro' => 'withdraw_totals.total_auto_ro',
                'total_transferred' => 'withdraw_totals.total_transferred',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            'datewd',
            'desc',
            'withdraw_totals.id'
        );
        $withdraws = $this->paginate($query, $request);

        return [
            'withdraws' => $withdraws,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
