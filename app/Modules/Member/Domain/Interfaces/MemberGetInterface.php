<?php

namespace App\Modules\Member\Domain\Interfaces;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Illuminate\Support\Collection;

interface MemberGetInterface
{
    public function findByUsername(string $username): ?ModelMember;

    public function findByHU(int $memberId): ?ModelNetwork;

    public function getTreeByUsername(string $username, ?ModelMember $member = null): ?ModelMember;

    public function getTreeByHU(int $memberId): ?ModelNetwork;

    public function searchMembers(string $keyword, int $limit = 10): Collection;

    public function searchDownlineMembers(ModelMember $member, string $keyword, int $limit = 10): Collection;

    public function getDownlineByUsername(string $username, ModelMember $member): ?ModelMember;
}
