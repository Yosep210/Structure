<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Product\Domain\Enums\OrderType;
use App\Modules\Product\Infrastructure\Eloquent\ModelShopOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetHistoryRoQuery
{
    use Filters, Paginates, Sorts;

    /**
     * @return array{ro: LengthAwarePaginator, sort: string, direction: string}
     */
    public function execute(Request $request, ModelMember $member, bool $isAdmin): array
    {
        $sortColumns = [
            'created_at' => 'shop_orders.created_at',
            'invoice' => 'shop_orders.invoice',
            'created_by' => 'activator_member.username',
            'username' => 'member.username',
            'product_list' => 'shop_orders.products',
            'total_checkout' => 'shop_orders.total_checkout',
            'payment_method' => 'shop_orders.payment_method',
            'status' => 'shop_orders.status',
            'dateconfirmed' => 'shop_orders.dateconfirmed',
        ];

        $query = ModelShopOrder::query()
            ->with(['details.product', 'details.package', 'details.varian'])
            ->join('member_networks as network', 'shop_orders.member_network_id', '=', 'network.id')
            ->join('members as member', 'network.member_id', '=', 'member.id')
            ->leftJoin('ros as ro', 'shop_orders.id', '=', 'ro.shop_order_id')
            ->leftJoin('member_networks as activator_network', 'ro.activator_id', '=', 'activator_network.id')
            ->leftJoin('members as activator_member', 'activator_network.member_id', '=', 'activator_member.id')
            ->whereIn('shop_orders.type_order', [
                OrderType::RO->value,
                OrderType::MANUAL_RO->value,
            ])
            ->select([
                'shop_orders.*',
                'member.username as member_username',
                'member.name as member_name',
                'activator_member.username as activator_username',
                'activator_member.name as activator_name',
            ]);

        if (! $isAdmin) {
            $query->where('network.member_id', $member->id);
        }

        if ($request->filled('product_list')) {
            $productName = $request->string('product_list')->toString();

            $query->whereHas('details', function (Builder $details) use ($productName): void {
                $details->where(function (Builder $details) use ($productName): void {
                    $details->whereHas('product', function (Builder $product) use ($productName): void {
                        $product->where('name', 'like', '%'.$productName.'%');
                    })->orWhereHas('package', function (Builder $package) use ($productName): void {
                        $package->where('name', 'like', '%'.$productName.'%');
                    });
                });
            });
        }

        $query = $this->filters($query, $request, [
            'text' => [
                'invoice' => 'shop_orders.invoice',
                'payment_method' => 'shop_orders.payment_method',
            ],
            'multi' => [
                'created_by' => ['activator_member.username', 'activator_member.name'],
                'username' => ['member.username', 'member.name'],
            ],
            'select' => [
                'status' => 'shop_orders.status',
            ],
            'nominal_range' => [
                'total_checkout' => 'shop_orders.total_checkout',
            ],
            'date' => [
                'created_at' => 'shop_orders.created_at',
                'dateconfirmed' => 'shop_orders.dateconfirmed',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            ['created_at' => 'desc'],
            tieBreaker: [
                'id' => 'desc',
            ],
        );

        $ro = $this->paginate($query, $request);

        $ro->getCollection()->each(function (ModelShopOrder $shopOrder): void {
            $shopOrder->setAttribute(
                'product_list',
                $shopOrder->details->map(fn ($detail): array => [
                    'name' => $detail->product?->name
                        ?? $detail->package?->name
                        ?? 'Produk',
                    'qty' => $detail->qty,
                ])->values()->all(),
            );
        });

        return [
            'ro' => $ro,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
