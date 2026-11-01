<?php

namespace App\Modules\AutoRo\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\AutoRo\Application\Queries\GetAutoRoHistoryQuery;
use App\Modules\AutoRo\Application\Queries\GetAutoRoListQuery;
use App\Modules\AutoRo\Application\Queries\GetAutoRoMonthlyQuery;
use App\Modules\AutoRo\Domain\Interfaces\AutoRoGetInterface;
use App\Modules\EWallet\Domain\Enums\EntryType;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AutoRoController extends Controller
{
    use HasAuth, Paginates;

    public function autoRoList(GetAutoRoListQuery $query): Response|RedirectResponse
    {
        // 1. [DAFTAR SALDO AUTO RO SELURUH MEMBER]
        $data = $query->execute($this->request);

        return Inertia::render('auto-ro/list', [
            'autoRo' => $data['autoRo'],
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'bonus_total_min',
                'bonus_total_max',
            ], $data),
        ]);
    }

    public function autoRoMonthly(GetAutoRoMonthlyQuery $query, AutoRoGetInterface $getRo): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [EKSEKUSI DATA AKUMULASI BULANAN AUTO RO]
        $data = $query->execute($this->request, $auth);

        // 2. [KHUSUS MEMBER]: Ambil ringkasan Total RO & Total RO Bulan Ini
        $roHistory = (! $auth['isAdmin'] && $auth['member']) ? $getRo->getRoHistory($auth['member']->username) : null;

        return Inertia::render('auto-ro/monthly', [
            'monthlies' => $data['monthlies'],
            'months' => $data['months'],
            'totalRo' => $roHistory['totalRo'] ?? null,
            'totalRoMonth' => $roHistory['totalRoMonth'] ?? null,
            'filters' => $this->getPaginatePayload($this->request, [
                'month_ro',
                'username',
                'name',
                'total_ro_min',
                'total_ro_max',
            ], $data),
        ]);
    }

    public function autoRoHistory(GetAutoRoHistoryQuery $query, AutoRoGetInterface $getRo, MemberGetInterface $getMember, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [UNTUK MEMBER LOGIN] Otomatis gunakan username milik akun member yang login
        if (! $auth['isAdmin'] && $username === null) {
            $username = $auth['member']?->username;
        }

        $targetMember = null;

        // 2. [UNTUK MEMBER LOGIN & ADMIN DENGAN REQ USERNAME]
        if ($username !== null) {
            $memberData = $getMember->findByUsername($username);
            if ($memberData === null) {
                throw MemberException::getUsername($username);
            }

            if (! $auth['isAdmin'] && $memberData->id !== $auth['member']?->id) {
                throw MemberException::getMemberNotFound($username);
            }

            // 3. [KHUSUS ADMIN DENGAN REQ URL /autoro/history/{username}]
            if ($auth['isAdmin'] && $this->request->route('username') !== null) {
                $targetMember = $memberData;
            }
        }

        $activeMember = $targetMember ?? (! $auth['isAdmin'] ? $auth['member'] : null);
        $from = (string) $this->request->input('from', '');

        $roHistory = null;
        $roEntry = null;

        // 4. [RINGKASAN SALDO / AKUMULASI AUTO RO SESUAI SUMBER HALAMAN ASAL]
        if ($activeMember !== null) {
            if ($from === 'list') {
                $roEntry = $getRo->getRoEntry($activeMember->username);
            } else {
                $roHistory = $getRo->getRoHistory($activeMember->username);
            }
        }

        // 5. [EKSEKUSI DATA MUTASI AUTO RO]
        $data = $query->execute($this->request, $username);

        return Inertia::render('auto-ro/history', [
            'from' => $from,
            'histories' => $data['histories'],
            'types' => EntryType::options(),
            'targetMember' => $targetMember ? [
                'id' => $targetMember->id,
                'name' => $targetMember->name,
                'username' => $targetMember->username,
            ] : null,
            'totalRo' => $roHistory['totalRo'] ?? null,
            'totalRoMonth' => $roHistory['totalRoMonth'] ?? null,
            'autoRoBalance' => $roEntry['autoRoBalance'] ?? null,
            'autoRoIn' => $roEntry['autoRoIn'] ?? null,
            'autoRoOut' => $roEntry['autoRoOut'] ?? null,
            'filters' => $this->getPaginatePayload($this->request, [
                'created_at_from',
                'created_at_to',
                'username',
                'name',
                'type',
                'nominal_min',
                'nominal_max',
                'amount_min',
                'amount_max',
                'description',
            ], $data),
        ]);
    }
}
