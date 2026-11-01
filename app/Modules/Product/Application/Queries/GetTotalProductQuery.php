<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Product\Infrastructure\Eloquent\ModelPin;
use App\Modules\Product\Infrastructure\Eloquent\ModelProduct;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class GetTotalProductQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?int $networkId = null): array
    {
        $request ??= new Request;

        $sortColumns = [
            'product_name' => 'products.name',
            'varian_name' => 'product_varians.varian',
            'price' => 'products.price',
            'stock_in' => 'stock_in',
            'total_stock' => 'total_stock',
            'stock_out' => 'stock_out',
        ];

        $pinTotals = ModelPin::query()
            ->select([
                'product_id',
                'varian_id',
            ])
            ->selectRaw('COUNT(*) as stock_in')
            ->selectRaw('SUM(CASE WHEN used = 0 OR used IS NULL THEN 1 ELSE 0 END) as total_stock')
            ->selectRaw('SUM(CASE WHEN used = 1 THEN 1 ELSE 0 END) as stock_out');

        if ($networkId !== null) {
            $pinTotals->where('pins.member_network_id', $networkId);
        }

        $pinTotals->groupBy(['product_id', 'varian_id']);

        $query = ModelProduct::query()
            ->leftJoin('product_varians', 'products.id', '=', 'product_varians.product_id')
            ->leftJoinSub($pinTotals, 'pin_totals', function (JoinClause $join) {
                $join->on('products.id', '=', 'pin_totals.product_id')
                    ->whereRaw('(
                        product_varians.id = pin_totals.varian_id
                        OR (
                            product_varians.id IS NULL
                            AND pin_totals.varian_id IS NULL
                        )
                    )');
            })
            ->select([
                'products.id',
                'products.name as product_name',
                'products.price',
                'product_varians.varian as varian_name',
            ])
            ->selectRaw('COALESCE(pin_totals.stock_in, 0) as stock_in')
            ->selectRaw('COALESCE(pin_totals.total_stock, 0) as total_stock')
            ->selectRaw('COALESCE(pin_totals.stock_out, 0) as stock_out')
            ->groupBy([
                'products.id',
                'products.name',
                'products.price',
                'product_varians.id',
                'product_varians.varian',
                'pin_totals.stock_in',
                'pin_totals.total_stock',
                'pin_totals.stock_out',
            ]);

        $query->whereNotNull('pin_totals.product_id');
        // $query->where('pin_totals.total_stock', '>', 0);

        $query = $this->filters($query, $request, [
            'text' => [
                'product_name' => 'products.name',
                'varian_name' => 'product_varians.varian',
            ],
            'nominal_range' => [
                'price' => 'products.price',
            ],
            'total_range' => [
                'stock_in' => 'stock_in',
                'total_stock' => 'total_stock',
                'stock_out' => 'stock_out',
            ],
        ]);

        $defaultSort = [
            'total_stock' => 'desc',
            'product_name' => 'asc',
        ];
        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            $defaultSort,
            tieBreaker: [
                'products.id' => 'desc',
                'product_varians.id' => 'asc',
            ]
        );
        $total = $this->paginate($query, $request);

        return [
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
