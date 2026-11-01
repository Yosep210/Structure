<?php

namespace App\Modules\EWallet\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\EWallet\Application\Queries\GetEWalletListQuery;
use App\Modules\EWallet\Domain\Enums\EntryType;
use App\Modules\EWallet\Domain\Interfaces\EWalletGetInterface;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Withdraw\Domain\Enums\StatusWithdraw;
use App\Modules\Withdraw\Domain\Enums\TypeWithdraw;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EWalletController extends Controller
{
    use HasAuth, Paginates;

    public function eWalletList(MemberGetInterface $getMember, EWalletGetInterface $getEwallet, GetEWalletListQuery $query, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [UNTUK MEMBER LOGIN] Otomatis gunakan username milik akun member yang login
        if (! $auth['isAdmin'] && $username === null) {
            $username = $auth['member']->username;
        }

        $targetMember = null;

        // 2. [UNTUK MEMBER LOGIN & ADMIN DENGAN REQ USERNAME]
        if ($username !== null) {
            $memberData = $getMember->findByUsername($username);
            if ($memberData === null || (! $auth['isAdmin'] && $memberData->id !== $auth['member']->id)) {
                throw MemberException::getUsername($username);
            }

            $targetMember = $memberData;
            $totals = $getEwallet->getEwalletMemberEntry($memberData->id);
        } else {
            // 3. [KHUSUS ADMIN TANPA REQ USERNAME] (Melihat daftar seluruh saldo member)
            $targetMember = null;
            $totals = $getEwallet->getAllEwalletEntry();
            $totalByWdStatus = $getEwallet->getAllByWdStatus();
        }

        $depositeIn = (float) ($totals->total_in ?? 0);
        $depositeOut = (float) ($totals->total_out ?? 0);
        $depositeSaldo = max(0, $depositeIn - $depositeOut);

        $depositeManual = $username === null
            ? max(0, (float) ($totalByWdStatus->firstWhere('wd_status', StatusWithdraw::WD_MANUAL->value)?->balance ?? 0))
            : 0;

        $depositeAuto = $username === null
            ? max(0, (float) ($totalByWdStatus->firstWhere('wd_status', StatusWithdraw::WD_AUTO->value)?->balance ?? 0))
            : 0;

        // 4. [EKSEKUSI DATA MUTASI / SALDO EWALLET]
        $data = $query->execute($this->request, $targetMember, $username);

        return Inertia::render('e-wallet/list', [
            'targetMember' => $targetMember,
            'depositeIn' => $depositeIn,
            'depositeOut' => $depositeOut,
            'depositeSaldo' => $depositeSaldo,
            'depositeManual' => $depositeManual,
            'depositeAuto' => $depositeAuto,
            'wallet' => $data['wallet'],
            'wd_statuses' => StatusWithdraw::options(),
            'sources' => TypeWithdraw::options(),
            'types' => EntryType::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'wd_status',
                'ewallet_balance_min',
                'ewallet_balance_max',
                'created_at_from',
                'created_at_to',
                'source',
                'type',
                'amount_min',
                'amount_max',
                'description',
            ], $data),
        ]);
    }
}
