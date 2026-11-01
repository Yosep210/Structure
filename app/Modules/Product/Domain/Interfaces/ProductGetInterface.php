<?php

namespace App\Modules\Product\Domain\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface ProductGetInterface
{
    public function getAllProduct(bool $withVariants = false): Collection;

    public function getAllPin(): int;

    public function getAllPinAvalilable(): int;

    public function getAllPinUsed(): int;

    public function getStockMember(int $networkId): int;

    public function getProductUsedMember(int $networkId): int;

    public function getTotalProductMember(int $networkId): int;
}
