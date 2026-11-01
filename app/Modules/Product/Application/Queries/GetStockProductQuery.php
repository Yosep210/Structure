<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Http\Request;

class GetStockProductQuery
{
    use Filters, Paginates, Sorts;

    public function __construct() {}

    public function execute(?Request $request = null): array
    {
        $sortColumns = [
            'username' => 'members.username',
            'name' => 'members.name',
            'total_products' => 'total_products',
            'stock_products' => 'stock_products',
            'total_active' => 'total_active',
        ];

        $query = ModelMember::onlyMembers()
            ->join('member_networks as network', 'network.member_id', '=', 'members.id')
            ->leftJoin('stockists', 'stockists.member_network_id', '=', 'network.id')
            ->select([
                'network.id',
                'members.username',
                'members.name',
                'stockists.id',
            ])
            ->selectRaw('(SELECT COUNT(*) FROM pins as p WHERE p.member_network_id = network.id) as total_products')
            ->selectRaw('(SELECT COUNT(*) FROM pins as p WHERE p.member_network_id = network.id AND (p.used = 0 OR p.used IS NULL)) as stock_products')
            ->selectRaw('(SELECT COUNT(*) FROM pins as p WHERE p.member_network_id = network.id AND p.status = 1 ) as total_active')
            ->groupBy('members.id', 'network.id');

        $query = $this->filters($query, $request, [
            'text' => [
                'username' => 'members.username',
                'name' => 'members.name',
            ],
            'total_range' => [
                'total_products' => 'total_products',
                'stock_products' => 'stock_products',
            ],
        ]);

        $defaultSort = [
            'total_active' => 'desc',
            'total_products' => 'desc',
        ];
        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            $defaultSort,
            tieBreaker: ['members.id' => 'desc']
        );
        $stockist = $this->paginate($query, $request);

        return [
            'stockist' => $stockist,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
