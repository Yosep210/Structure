<?php

namespace App\Concerns;

use App\Enums\MemberRole;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;

trait HasAuth
{
    /**
     * @return array{member: ModelMember|null, isAdmin: bool}
     */
    protected function getAuthMember(): array
    {
        /** @var ModelMember|null $member */
        $member = auth()->user();

        return [
            'member' => $member,
            'isAdmin' => $member ? $member->hasRole(MemberRole::ADMIN->value) : false,
        ];
    }
}
