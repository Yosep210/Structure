<?php

namespace App\Modules\Member\Infrastructure\Repositories;

use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Domain\Interfaces\MemberPostInterface;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Illuminate\Support\Collection;

class MemberRepository implements MemberGetInterface, MemberPostInterface
{
    public function __construct(
        protected ModelMember $model
    ) {}

    public function searchMembers(string $keyword, int $limit = 10): Collection
    {
        return $this->model->onlyMembers()
            ->where(function ($query) use ($keyword) {
                $query->where('username', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%');
            })
            ->orderByRaw('CASE WHEN username = ? THEN 0 WHEN username LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END', [
                $keyword,
                $keyword.'%',
                $keyword.'%',
            ])
            ->limit($limit)
            ->get(['id', 'username', 'name']);
    }

    public function searchDownlineMembers(ModelMember $member, string $keyword, int $limit = 10): Collection
    {
        $authNode = $this->getTreeByHU($member->id);
        if ($authNode === null) {
            return collect();
        }

        return $this->model->onlyMembers()
            ->where('members.id', '!=', $member->id)
            ->whereHas('network', function ($nq) use ($authNode) {
                $nq->where('hu_index', 1)
                    ->where('tree', 'like', $authNode->tree.'-%');
            })
            ->where(function ($query) use ($keyword) {
                $query->where('username', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%');
            })
            ->orderByRaw('CASE WHEN username = ? THEN 0 WHEN username LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END', [
                $keyword,
                $keyword.'%',
                $keyword.'%',
            ])
            ->limit($limit)
            ->get(['id', 'username', 'name']);
    }

    public function getDownlineByUsername(string $username, ModelMember $member): ?ModelMember
    {
        $authNode = $this->getTreeByHU($member->id);
        if ($authNode === null) {
            return null;
        }

        return $this->model->onlyMembers()
            ->where('members.id', '!=', $member->id)
            ->where('username', $username)
            ->whereHas('network', function ($nq) use ($authNode) {
                $nq->where('hu_index', 1)
                    ->where('tree', 'like', $authNode->tree.'-%');
            })
            ->first();
    }

    public function findByHU(int $memberId): ?ModelNetwork
    {
        return ModelNetwork::query()
            ->where('member_id', $memberId)
            ->where('hu_index', 1)
            ->first();
    }

    public function findByUsername(string $username): ?ModelMember
    {
        return $this->model->onlyMembers()->where('username', $username)->first();
    }

    public function getTreeByUsername(string $username, ?ModelMember $member = null): ?ModelMember
    {
        $query = ModelMember::query()
            ->where('username', 'like', '%'.$username.'%');

        if ($member !== null) {
            $authNode = $this->getTreeByHU($member->id);
            if ($authNode !== null) {
                $query->where(function ($q) use ($member, $authNode) {
                    $q->where('members.id', $member->id)
                        ->orWhereHas('network', function ($nq) use ($authNode) {
                            $nq->where('hu_index', 1)
                                ->where('tree', 'like', $authNode->tree.'-%');
                        });
                });
            }
        }

        return $query
            ->orderByRaw('CASE WHEN username = ? THEN 0 WHEN username LIKE ? THEN 1 ELSE 2 END', [$username, $username.'%'])
            ->first();
    }

    public function getTreeByHU(int $memberId): ?ModelNetwork
    {
        return ModelNetwork::with(['member', 'parent.member'])
            ->where('member_id', $memberId)
            ->where('hu_index', 1)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->model->newQuery()->where('id', $id)->update($data);
    }
}
