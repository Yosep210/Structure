<?php

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Product\Domain\Enums\PinStatus;
use App\Modules\Product\Domain\Interfaces\ProductGetInterface;
use App\Modules\Product\Domain\Interfaces\ProductPostInterface;
use App\Modules\Product\Infrastructure\Eloquent\ModelPin;
use App\Modules\Product\Infrastructure\Eloquent\ModelProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductGetInterface, ProductPostInterface
{
    public function __construct(
        protected ModelProduct $model
    ) {}

    public function getAllProduct(bool $withVariants = false): Collection
    {
        $query = $this->model->where('status', GeneralStatus::ACTIVE->value);

        if ($withVariants) {
            $query->with(['varians' => function ($query) {
                $query->select(['id', 'product_id', 'varian'])
                    ->where('status', GeneralStatus::ACTIVE->value);
            }]);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function getAllPin(): int
    {
        return ModelPin::query()->count();
    }

    public function getAllPinAvalilable(): int
    {
        return ModelPin::query()
            ->where(function (Builder $query) {
                $query->where('used', false)->orWhereNull('used');
            })
            ->count();
    }

    public function getAllPinUsed(): int
    {
        return ModelPin::query()->where('used', true)->count();
    }

    public function getStockMember(int $networkId): int
    {
        return ModelPin::query()
            ->where('member_network_id', $networkId)
            ->count();
    }

    public function getTotalProductMember(int $networkId): int
    {
        return ModelPin::query()
            ->where('member_network_id', $networkId)
            ->where('status', PinStatus::RESERVED)
            ->count();
    }

    public function getProductUsedMember(int $networkId): int
    {
        return ModelPin::query()
            ->where('member_network_id', $networkId)
            ->where('status', PinStatus::USED)
            ->count();
    }
}
