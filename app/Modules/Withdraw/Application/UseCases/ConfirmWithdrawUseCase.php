<?php

namespace App\Modules\Withdraw\Application\UseCases;

use App\Modules\Withdraw\Domain\Enums\WithdrawStatus;
use App\Modules\Withdraw\Domain\Exceptions\WithdrawException;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawGetInterface;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawPostInterface;
use App\Modules\Withdraw\Infrastructure\Eloquent\ModelWithdraw;
use Illuminate\Support\Facades\DB;

class ConfirmWithdrawUseCase
{
    public function __construct(
        protected WithdrawGetInterface $get,
        protected WithdrawPostInterface $post,
    ) {}

    public function execute(int $id, string $adminUsername): ModelWithdraw
    {
        return DB::transaction(function () use ($id, $adminUsername) {
            $withdraw = $this->get->getByWdId($id, lockForUpdate: true);

            if ($withdraw === null) {
                throw WithdrawException::notFound('Transaksi withdraw');
            }

            if ($withdraw->status !== WithdrawStatus::PENDING) {
                throw WithdrawException::withdrawAlreadyProses();
            }

            $this->post->confirm($id, $adminUsername);

            return $withdraw;
        });
    }
}
