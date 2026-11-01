<?php

namespace App\Modules\Member\Application\UseCases;

use App\Modules\Member\Domain\Enums\StatusMember;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Domain\Interfaces\MemberPostInterface;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;

class MemberStatusUseCase
{
    public function __construct(
        protected MemberGetInterface $get,
        protected MemberPostInterface $post,
    ) {}

    public function execute(string $username, StatusMember $newStatus): ModelMember
    {
        $member = $this->get->findByUsername($username);

        if ($member === null) {
            throw MemberException::getUsername($username);
        }

        $this->post->update($member->id, [
            'status' => $newStatus,
        ]);

        return $member;
    }
}
