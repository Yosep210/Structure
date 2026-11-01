<?php

namespace App\Modules\Withdraw\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\Withdraw\Application\Queries\GetWithdrawListQuery;
use App\Modules\Withdraw\Application\Queries\GetWithdrawTotalQuery;
use App\Modules\Withdraw\Application\UseCases\ConfirmWithdrawUseCase;
use App\Modules\Withdraw\Domain\Enums\WithdrawStatus;
use App\Modules\Withdraw\Domain\Enums\WithdrawType;
use App\Modules\Withdraw\Domain\Exceptions\WithdrawException;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawGetInterface;
use App\Modules\Withdraw\Http\Requests\ConfirmWithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawController extends Controller
{
    use HasAuth, Paginates;

    public function withdrawList(GetWithdrawListQuery $query, WithdrawGetInterface $getWd): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [RESOLUSI ROLE & FILTER USERNAME]
        // Member otomatis dibatasi pada riwayat miliknya, Admin melihat data seluruh member
        $username = ! $auth['isAdmin'] ? $auth['member']?->username : null;

        // 2. [RINGKASAN TOTAL WITHDRAW]
        $totals = $getWd->getWithdrawTotals($username);

        // 3. [EKSEKUSI QUERY DAFTAR WITHDRAW]
        $data = $query->execute($this->request, $username);

        return Inertia::render('withdraw/list', [
            ...$totals,
            'ewallet_balance' => ! $auth['isAdmin'] ? (float) ($auth['member']?->ewallet_balance ?? 0) : null,
            'withdraws' => $data['withdraws'],
            'wd_statuses' => WithdrawStatus::options(),
            'types' => collect([WithdrawType::EWALLET])->map(fn ($case) => [
                'value' => (string) $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ])->values()->all(),
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'type',
                'bank_code',
                'bank_name',
                'bank_account',
                'bank_account_name',
                'nominal_min',
                'nominal_max',
                'nominal_receipt_min',
                'nominal_receipt_max',
                'status',
                'created_at_from',
                'created_at_to',
                'dateconfirmed_from',
                'dateconfirmed_to',
                'confirm_by',
                'admin_fund',
            ], $data),
        ]);
    }

    public function withdrawTotal(GetWithdrawTotalQuery $query, WithdrawGetInterface $getWd): Response|RedirectResponse
    {
        // 1. [KHUSUS ADMIN: REKAP AKUMULASI DANA WITHDRAW]
        $totals = $getWd->getWithdrawTotals();

        $data = $query->execute($this->request);

        return Inertia::render('withdraw/total', [
            ...$totals,
            'withdraws' => $data['withdraws'],
            'filters' => $this->getPaginatePayload($this->request, [
                'datewd_from',
                'datewd_to',
                'trx_min',
                'trx_max',
                'total_nominal_min',
                'total_nominal_max',
                'total_receipt_min',
                'total_receipt_max',
                'total_admin_min',
                'total_admin_max',
                'total_auto_ro_min',
                'total_auto_ro_max',
                'total_transferred_min',
                'total_transferred_max',
            ], $data),
        ]);
    }

    public function confirm(ConfirmWithdrawRequest $request, ConfirmWithdrawUseCase $useCase, int $id): RedirectResponse
    {
        // 1. [KHUSUS ADMIN: KONFIRMASI PENCAIRAN DANA WITHDRAW]
        $adminUsername = $this->request->user()->username;
        $withdraw = $useCase->execute($id, $adminUsername);

        $username = $withdraw->member?->username ?? $withdraw->bank_account_name ?? 'Member';

        throw WithdrawException::wdConfirmed($username, $withdraw);
    }
}
