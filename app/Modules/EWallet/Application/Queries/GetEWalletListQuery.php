<?php

namespace App\Modules\EWallet\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\EWallet\Infrastructure\Eloquent\ModelEWallet;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Withdraw\Domain\Enums\TypeWithdraw;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetEWalletListQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?object $targetMember = null, ?string $username = null): array
    {
        // =========================================================================
        // [1. MODE ADMIN: DAFTAR SALDO SELURUH MEMBER] ($username === null)
        // =========================================================================
        if ($username === null) {
            $sortColumns = [
                'id' => 'members.id',
                'username' => 'members.username',
                'name' => 'members.name',
                'wd_status' => 'profiles.wd_status',
                'wd_min' => 'profiles.wd_min',
                'ewallet_balance' => 'members.ewallet_balance',
            ];

            $query = ModelMember::onlyMembers()
                ->join('member_networks as network', function ($query) {
                    $query->on('network.member_id', '=', 'members.id')
                        ->where('network.hu_index', '=', 1);
                })
                ->leftJoin('member_profiles as profiles', 'profiles.member_id', '=', 'members.id')
                ->select([
                    'members.id',
                    'members.username',
                    'members.name',
                    'profiles.wd_status',
                    'profiles.wd_min',
                    'members.ewallet_balance',
                ]);

            $query = $this->filters($query, $request, [
                'text' => [
                    'username' => 'members.username',
                    'name' => 'members.name',
                ],
                'select' => [
                    'wd_status' => 'profiles.wd_status',
                ],
                'nominal_range' => [
                    'ewallet_balance' => 'members.ewallet_balance',
                ],
            ]);

            $defaultSort = 'ewallet_balance';
        }
        // =========================================================================
        // [2. MODE MEMBER / ADMIN AUDIT: RIWAYAT MUTASI E-WALLET] ($username !== null)
        // =========================================================================
        else {
            $sortColumns = [
                'id' => 'ewallets.id',
                'created_at' => 'ewallets.created_at',
                'source' => 'ewallets.source',
                'type' => 'ewallets.type',
                'amount' => 'ewallets.amount',
                'description' => 'ewallets.description',
                'nominal' => 'ewallets.nominal',
                'tax' => 'ewallets.tax',
            ];

            $query = ModelEWallet::query()
                ->join('members', 'members.id', '=', 'ewallets.member_id')
                ->where('ewallets.status', GeneralStatus::ACTIVE->value)
                ->where('ewallets.source', TypeWithdraw::Bonus->value)
                ->select([
                    'ewallets.id',
                    'ewallets.created_at',
                    'ewallets.source',
                    'ewallets.type',
                    'ewallets.amount',
                    'ewallets.description',
                    'ewallets.nominal',
                    'ewallets.tax',
                ])
                ->when($username !== null, function (Builder $query) use ($username) {
                    $query->where('members.username', $username);
                });

            $query = $this->filters($query, $request, [
                'date' => [
                    'created_at' => 'ewallets.created_at',
                ],
                'exact' => [
                    'source' => 'ewallets.source',
                    'type' => 'ewallets.type',
                ],
                'range' => [
                    'amount' => 'ewallets.amount',
                ],
                'text' => [
                    'description' => 'ewallets.description',
                ],
            ]);

            $defaultSort = [
                'created_at' => 'desc',
                'id' => 'desc',
            ];
        }

        ['sort' => $sort, 'direction' => $direction] = $this->sort($query, $request, $sortColumns, $defaultSort, 'desc', ['id' => 'desc']);
        $wallet = $this->paginate($query, $request);

        return [
            'wallet' => $wallet,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
