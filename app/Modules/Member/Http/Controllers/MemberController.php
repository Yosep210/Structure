<?php

namespace App\Modules\Member\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\Member\Application\Queries\GetBinaryTreeQuery;
use App\Modules\Member\Application\Queries\GetGenerationListQuery;
use App\Modules\Member\Application\Queries\GetGenerationTreeQuery;
use App\Modules\Member\Application\Queries\GetMemberListQuery;
use App\Modules\Member\Application\Queries\GetReportRegQuery;
use App\Modules\Member\Application\Queries\GetSponsorListQuery;
use App\Modules\Member\Application\UseCases\MemberStatusUseCase;
use App\Modules\Member\Domain\Enums\PositionMember;
use App\Modules\Member\Domain\Enums\RankMember;
use App\Modules\Member\Domain\Enums\RegStatus;
use App\Modules\Member\Domain\Enums\RegType;
use App\Modules\Member\Domain\Enums\StatusMember;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Http\Requests\MemberStatusRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    use HasAuth, Paginates;

    public function memberList(GetMemberListQuery $query): Response
    {
        $data = $query->execute($this->request);

        return Inertia::render('member/list', [
            'member' => $data['member'],
            'ranks' => RankMember::options(),
            'statuses' => StatusMember::options(),
            'positions' => PositionMember::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'rank_id',
                'contact',
                'sponsor',
                'parent',
                'position',
                'status',
                'created_at_from',
                'created_at_to',
                'last_login_from',
                'last_login_to',
            ], $data),
        ]);
    }

    public function memberStatus(MemberStatusRequest $request, MemberStatusUseCase $useCase, string $username): Response|RedirectResponse
    {
        $newStatus = StatusMember::from((int) $request->validated('status'));

        $member = $useCase->execute($username, $newStatus);

        throw MemberException::statusUpdate($member->username, $newStatus);
    }

    public function sponsorList(GetSponsorListQuery $query): Response
    {
        $data = $query->execute($this->request);

        return Inertia::render('member/sponsor', [
            'sponsor' => $data['sponsor'],
            'ranks' => RankMember::options(),
            'statuses' => StatusMember::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'rank_id',
                'status',
                'total_active_min',
                'total_active_max',
                'total_non_active_min',
                'total_non_active_max',
                'created_at_from',
                'created_at_to',
            ], $data),
        ]);
    }

    public function generationList(MemberGetInterface $get, GetGenerationListQuery $query, GetGenerationTreeQuery $tree, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [UNTUK MEMBER LOGIN / TARGET MEMBER]
        // Jika $username null, gunakan akun member yang login. Jika ada $username, cari member target.
        $targetMember = $username === null
            ? $auth['member']
            : $get->findByUsername($username);

        if ($targetMember === null) {
            throw MemberException::getUsername($username);
        }

        $targetNode = $get->findByHU($targetMember->id);

        if ($targetNode === null) {
            throw MemberException::getNetworkNotFound($targetMember->username);
        }

        // 2. [PROTEKSI AKSES JARINGAN MEMBER NON-ADMIN]
        // Member hanya diperbolehkan melihat pohon generasi downline miliknya sendiri
        if (! $auth['isAdmin'] && $targetMember->id !== $auth['member']->id) {
            $authenticatedNode = $get->findByHU($auth['member']->id);
            $isDownline = $authenticatedNode !== null
                && $targetNode->gen > $authenticatedNode->gen
                && str_starts_with(
                    (string) $targetNode->tree_sponsor,
                    (string) $authenticatedNode->tree_sponsor.'-',
                );

            if (! $isDownline) {
                throw MemberException::getMemberNotFound($targetMember->username);
            }
        }

        // 3. [EKSEKUSI QUERY GENERASI BERDASARKAN MODE VIEW]
        $data = $query->execute($this->request, $targetMember, $targetNode, $auth['isAdmin'], $username);

        return Inertia::render('member/generation', [
            'generation' => $data['generation'],
            'ranks' => RankMember::options(),
            'generationTree' => fn () => $tree->execute(
                $targetMember,
                $targetNode,
                ! $auth['isAdmin'] || $username !== null,
            ),
            'username' => $username,
            'filters' => $this->getPaginatePayload($this->request, [
                'member_username',
                'sponsor_username',
                'gen_min',
                'gen_max',
                'group_min',
                'group_max',
                'total_omzet_min',
                'total_omzet_max',
            ], $data),
        ]);
    }

    public function binaryMember(MemberGetInterface $get, GetBinaryTreeQuery $query, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        // 1. [MENENTUKAN TARGET NODE BINARY]
        // Jika parameter username tidak disertakan, targetkan akun yang sedang login
        $searchParam = $username ?? $this->request->input('username');
        $targetMember = $searchParam === null ? $auth['member'] : $get->getTreeByUsername($searchParam, $auth['isAdmin'] ? null : $auth['member']);
        if ($targetMember === null) {
            throw MemberException::getUsername($searchParam);
        }

        $targetNode = $get->getTreeByHU($targetMember->id);
        if ($targetNode === null) {
            throw MemberException::getNetworkNotFound($targetMember->username);
        }

        // 2. [VALIDASI KEPEMILIKAN JARINGAN DOWNLINE UNTUK MEMBER]
        $authNode = $get->getTreeByHU($auth['member']->id);
        if (! $auth['isAdmin'] && $targetMember->id !== $auth['member']->id) {
            $isDownline = $authNode !== null && $targetNode->level > $authNode->level && str_starts_with((string) $targetNode->tree, (string) $authNode->tree.'-');
            if (! $isDownline) {
                throw MemberException::getMemberNotFound($targetMember->username);
            }
        }

        // 3. [NAVIGASI UPLINE TREE]
        $showUpButton = $targetNode->parent_id !== null && ($auth['isAdmin'] || ($authNode !== null && $targetNode->id !== $authNode->id));
        $parentUsername = $showUpButton ? $targetNode->parent?->member?->username : null;

        return Inertia::render('member/binary', [
            'username' => $username,
            'binaryTree' => fn () => $query->execute($targetNode),
            'navigation' => [
                'showUpButton' => $showUpButton,
                'parentUsername' => $parentUsername,
            ],
            'ranks' => RankMember::options(),
        ]);
    }

    public function reportReg(GetReportRegQuery $query): Response
    {
        $auth = $this->getAuthMember();
        $member = $auth['isAdmin'] ? null : $auth['member'];
        $data = $query->execute($this->request, $member);

        return Inertia::render('member/report', [
            'report' => $data['report'],
            'accesses' => RegType::options(),
            'statuses' => RegStatus::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'pendaftar',
                'member',
                'contact',
                'sponsor',
                'access',
                'status',
                'created_at_from',
                'created_at_to',
                'dateconfirmed_from',
                'dateconfirmed_to',
            ], $data),
        ]);
    }
}
