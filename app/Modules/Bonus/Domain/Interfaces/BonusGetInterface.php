<?php

namespace App\Modules\Bonus\Domain\Interfaces;

interface BonusGetInterface
{
    public function getAllAmount(): float;

    public function getAllMemberAmount($username): float;
}
