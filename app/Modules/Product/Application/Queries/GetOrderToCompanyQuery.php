<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Product\Domain\Enums\OrderStatus;
use App\Modules\Product\Domain\Enums\OrderTab;
use App\Modules\Product\Domain\Enums\OrderType;
use App\Modules\Product\Infrastructure\Eloquent\ModelShopOrder;
use App\Modules\Product\Infrastructure\Eloquent\ModelShopOrderDetail;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class GetOrderToCompanyQuery
{
    use Filters, Paginates, Sorts;

    public function execute(Request $request, OrderTab $tab = OrderTab::PENDING): array
    {
        $sortColumns = [
            'invoice' => 'shop_orders.invoice',
            'name' => 'member.name',
            'username' => 'member.username',
            'product_name' => 'order_product_names.product_name',
            'type_order' => 'shop_orders.type_order',
            'total_checkout' => 'shop_orders.total_checkout',
            'total_payment' => 'shop_orders.total_payment',
            'payment_method' => 'shop_orders.payment_method',
            'resi' => 'shop_orders.resi',
            'status' => 'shop_orders.status',
            'created_at' => 'shop_orders.created_at',
            'updated_at' => 'shop_orders.updated_at',
            'dateconfirmed' => 'shop_orders.dateconfirmed',
            'dateexpired' => 'shop_orders.dateexpired',
            'confirmed_by' => 'shop_orders.confirmed_by',
            'cancelled_by' => 'shop_orders.modified_by',
        ];

        $query = ModelShopOrder::query()
            ->with(['details.product', 'details.package', 'details.varian'])
            ->join('member_networks as network', 'shop_orders.member_network_id', '=', 'network.id')
            ->join('members as member', 'network.member_id', '=', 'member.id')
            ->where(function ($q) {
                $q->whereNull('shop_orders.stockist_id')
                    ->orWhere('shop_orders.stockist_id', 0);
            })
            ->select([
                'shop_orders.*',
                'member.id as member_id',
                'member.name as member_name',
                'member.username as member_username',
            ]);

        match ($tab) {
            OrderTab::PENDING => $query->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('shop_orders.status', OrderStatus::PENDING->value)
                        ->whereIn('shop_orders.type_order', [
                            OrderType::MEMBER->value,
                            OrderType::MANUAL_RO->value,
                        ]);
                })->orWhere(function ($query) {
                    $query->where('shop_orders.status', OrderStatus::PROCESSED->value)
                        ->where('shop_orders.payment_shipping_status', 0)
                        ->where(function ($query) {
                            $query->whereNull('shop_orders.resi')
                                ->orWhere('shop_orders.resi', '');
                        })
                        ->whereIn('shop_orders.type_order', [
                            OrderType::MEMBER->value,
                            OrderType::MANUAL_RO->value,
                        ]);
                });
            }),
            OrderTab::CONFIRM => $query->where('shop_orders.status', OrderStatus::PROCESSED->value)
                ->where('shop_orders.payment_shipping_status', 1)
                ->where(function ($query) {
                    $query->whereNull('shop_orders.resi')
                        ->orWhere('shop_orders.resi', '');
                }),
            OrderTab::DONE => $query->where('shop_orders.status', OrderStatus::PROCESSED->value)
                ->whereNotNull('shop_orders.resi')
                ->where('shop_orders.resi', '<>', ''),
            OrderTab::CANCEL => $query->whereIn('shop_orders.status', [
                OrderStatus::CANCELED->value,
            ]),
        };

        if ($request->filled('product_name')) {
            $productName = $request->input('product_name');

            $query->whereHas('details', function ($query) use ($productName) {
                $query->where(function ($query) use ($productName) {
                    $query->whereHas('product', function ($query) use ($productName) {
                        $query->where('name', 'like', '%'.$productName.'%');
                    })->orWhereHas('package', function ($query) use ($productName) {
                        $query->where('name', 'like', '%'.$productName.'%');
                    });
                });
            });
        }

        $query = $this->filters($query, $request, [
            'text' => [
                'invoice' => 'shop_orders.invoice',
                'username' => 'member.username',
                'resi' => 'shop_orders.resi',
                'payment_method' => 'shop_orders.payment_method',
                'confirmed_by' => 'shop_orders.confirmed_by',
                'cancelled_by' => 'shop_orders.modified_by',
            ],
            'multi' => [
                'name' => ['member.name', 'member.username'],
            ],
            'select' => [
                'type_order' => 'shop_orders.type_order',
                'status' => 'shop_orders.status',
            ],
            'nominal_range' => [
                'total_checkout' => 'shop_orders.total_checkout',
            ],
            'date' => [
                'created_at' => 'shop_orders.created_at',
                'cancelled_at' => 'shop_orders.updated_at',
                'dateconfirmed' => 'shop_orders.dateconfirmed',
                'dateexpired' => 'shop_orders.dateexpired',
            ],
        ]);

        if ($request->input('sort') === 'product_name') {
            $productNames = ModelShopOrderDetail::query()
                ->leftJoin('products as product', 'product.id', '=', 'shop_order_details.product_id')
                ->leftJoin('product_packages as package', 'package.id', '=', 'shop_order_details.product_package_id')
                ->select('shop_order_details.shop_order_id')
                ->selectRaw('MIN(COALESCE(product.name, package.name)) as product_name')
                ->groupBy('shop_order_details.shop_order_id');

            $query->leftJoinSub($productNames, 'order_product_names', function (JoinClause $join): void {
                $join->on('order_product_names.shop_order_id', '=', 'shop_orders.id');
            });
        }

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            ['created_at' => 'desc'],
            tieBreaker: [
                'id' => 'desc',
            ]
        );
        $order = $this->paginate($query, $request);

        return [
            'order' => $order,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
