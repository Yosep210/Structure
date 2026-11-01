<?php

namespace App\Modules\Withdraw\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Withdraw\Infrastructure\Eloquent\ModelWithdraw;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetWithdrawListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?string $username = null): array
    {
        $sortColumns = [
            'username' => 'members.username',
            'name' => 'members.name',
            'type' => 'withdrawals.type',
            'bank_code' => 'withdrawals.bank_code',
            'bank_name' => 'withdrawals.bank_name',
            'bank_account' => 'withdrawals.bank_account',
            'bank_account_name' => 'withdrawals.bank_account_name',
            'nominal' => 'withdrawals.nominal',
            'nominal_receipt' => 'withdrawals.nominal_receipt',
            'status' => 'withdrawals.status',
            'created_at' => 'withdrawals.created_at',
            'dateconfirmed' => 'withdrawals.dateconfirmed',
            'confirm_by' => 'withdrawals.confirm_by',
            'admin_fund' => 'withdrawals.admin_fund',
        ];

        $query = ModelWithdraw::query()
            ->join('members', 'withdrawals.member_id', '=', 'members.id')
            ->select([
                'username' => 'members.username',
                'name' => 'members.name',
                'id' => 'withdrawals.id',
                'type' => 'withdrawals.type',
                'bank_code' => 'withdrawals.bank_code',
                'bank_name' => 'withdrawals.bank_name',
                'bank_account' => 'withdrawals.bank_account',
                'bank_account_name' => 'withdrawals.bank_account_name',
                'nominal' => 'withdrawals.nominal',
                'nominal_receipt' => 'withdrawals.nominal_receipt',
                'status' => 'withdrawals.status',
                'created_at' => 'withdrawals.created_at',
                'dateconfirmed' => 'withdrawals.dateconfirmed',
                'confirm_by' => 'withdrawals.confirm_by',
                'admin_fund' => 'withdrawals.admin_fund',
            ])
            ->when($username !== null, function (Builder $query) use ($username) {
                $query->where('members.username', $username);
            });

        $query = $this->filters($query, $request, [
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
                'confirm_by' => 'withdrawals.confirm_by',
            ],
            'select' => [
                'type' => 'withdrawals.type',
                'status' => 'withdrawals.status',
            ],
            'nominal_range' => [
                'nominal' => 'withdrawals.nominal',
                'nominal_receipt' => 'withdrawals.nominal_receipt',
            ],
            'date' => [
                'created_at' => 'withdrawals.created_at',
                'dateconfirmed' => 'withdrawals.dateconfirmed',
            ],
            'multi' => [
                'bank_account' => [
                    'withdrawals.bank_code',
                    'withdrawals.bank_name',
                    'withdrawals.bank_account',
                    'withdrawals.bank_account_name',
                ],
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            defaultSort: [
                'created_at' => 'desc',
                'username' => 'asc',
            ]
        );
        $withdraws = $this->paginate($query, $request);

        return [
            'withdraws' => $withdraws,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
