<?php

namespace App\Modules\Member\Domain\Interfaces;

interface MemberPostInterface
{
    public function update(int $id, array $data): bool;
}
