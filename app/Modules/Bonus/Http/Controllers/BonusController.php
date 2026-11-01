<?php

namespace App\Modules\Bonus\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\Bonus\Application\Queries\GetBonusHistoryQuery;
use App\Modules\Bonus\Application\Queries\GetBonusListQuery;
use App\Modules\Bonus\Application\Queries\GetStatementMemberQuery;
use App\Modules\Bonus\Domain\Enums\BonusType;
use App\Modules\Bonus\Domain\Interfaces\BonusGetInterface;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BonusController extends Controller
{
    use HasAuth, Paginates;

    public function bonusList(BonusGetInterface $get, GetBonusListQuery $query): Response
    {
        $isStatement = $this->request->routeIs('bonus.statements');

        $defaultDateFrom = $isStatement ? now()->startOfMonth()->toDateString() : '';
        $defaultDateTo = $isStatement ? now()->toDateString() : '';
        $dateFrom = (string) $this->request->input('date_from', $defaultDateFrom);
        $dateTo = (string) $this->request->input('date_to', $defaultDateTo);

        $data = $query->execute($this->request, $dateFrom, $dateTo, $isStatement);

        $totalBonus = ! $isStatement ? $get->getAllAmount() : null;

        return Inertia::render('bonus/list', [
            'totalBonus' => $totalBonus,
            'isStatement' => $isStatement,
            'bonus' => $data['bonus'],
            'defaultFilters' => $isStatement ? [
                'date_from' => $defaultDateFrom,
                'date_to' => $defaultDateTo,
            ] : null,
            'filters' => array_merge(
                $this->getPaginatePayload($this->request, [
                    'date_from',
                    'date_to',
                    'username',
                    'name',
                    'amount_min',
                    'amount_max',
                ], $data),
                [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
            ),
        ]);
    }

    public function bonusHistory(GetBonusHistoryQuery $query, MemberGetInterface $getMember, BonusGetInterface $getBonus, ?string $username = null): Response|RedirectResponse
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
            if ($memberData === null) {
                throw MemberException::getUsername($username);
            }

            if (! $auth['isAdmin'] && $memberData->id !== $auth['member']->id) {
                throw MemberException::getMemberNotFound($username);
            }

            // 3. [KHUSUS ADMIN DENGAN REQ URL /bonus/history/{username}]
            if ($auth['isAdmin'] && $this->request->route('username') !== null) {
                $targetMember = $memberData;
            }

            $totalBonus = $getBonus->getAllMemberAmount($username);
        } else {
            // 4. [KHUSUS ADMIN TANPA REQ /bonus/history] (Melihat seluruh riwayat bonus semua member)
            $totalBonus = $getBonus->getAllAmount();
        }

        $data = $query->execute($this->request, $username);

        return Inertia::render('bonus/history', [
            'totalBonus' => $totalBonus,
            'bonus' => $data['bonus'],
            'targetMember' => $targetMember,
            'username' => $username,
            'types' => BonusType::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'created_at_from',
                'created_at_to',
                'username',
                'name',
                'amount_min',
                'amount_max',
                'type',
                'description',
            ], $data),
        ]);
    }

    public function statementMember(GetStatementMemberQuery $query, MemberGetInterface $getMember, ?string $username = null): Response|RedirectResponse
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

            // 3. [KHUSUS ADMIN DENGAN REQ URL /bonus/statement/{username}]
            if ($auth['isAdmin'] && $this->request->route('username') !== null) {
                $targetMember = $memberData;
            }
        } else {
            $targetMember = $auth['member'];
        }

        $dateFrom = (string) $this->request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = (string) $this->request->input('date_to', now()->toDateString());

        $data = $query->execute($username, $dateFrom, $dateTo);

        return Inertia::render('bonus/statement-member', [
            'targetMember' => ($auth['isAdmin'] && $targetMember) ? [
                'id' => $targetMember->id,
                'name' => $targetMember->name,
                'username' => $targetMember->username,
                'email' => $targetMember->email,
            ] : null,
            'username' => ($auth['isAdmin'] && $targetMember) ? $targetMember->username : null,
            'items' => $data['items'],
            'summary' => $data['summary'],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
