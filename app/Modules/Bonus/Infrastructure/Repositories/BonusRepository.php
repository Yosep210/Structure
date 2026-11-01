<?php

namespace App\Modules\Bonus\Infrastructure\Repositories;

use App\Modules\Bonus\Domain\Interfaces\BonusGetInterface;
use App\Modules\Bonus\Domain\Interfaces\BonusPostInterface;
use App\Modules\Bonus\Infrastructure\Eloquent\ModelBonus;

class BonusRepository implements BonusGetInterface, BonusPostInterface
{
    public function __construct(
        protected ModelBonus $model
    ) {}

    public function getAllAmount(): float
    {
        return (float) $this->model->sum('amount');
    }

    public function getAllMemberAmount($username): float
    {
        return (float) $this->model
            ->from('bonuses as bonus')
            ->join('member_networks as network', 'network.id', '=', 'bonus.member_network_id')
            ->join('members', 'members.id', '=', 'network.member_id')
            ->where('members.username', $username)
            ->sum('bonus.amount');
    }
}
