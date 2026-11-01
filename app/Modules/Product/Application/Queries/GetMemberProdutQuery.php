<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Product\Infrastructure\Eloquent\ModelPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetMemberProdutQuery
{
    use Filters, Paginates, Sorts;

    public function execute(int $networkId, ?Request $request = null): array
    {
        $request ??= new Request;

        $sortColumns = [
            'id_pin' => 'pins.id_pin',
            'stockist_username' => 'stockist_member.username',
            'product_name' => 'products.name',
            'varian_name' => 'product_varians.varian',
            'status' => 'pins.status',
            'created_ad' => 'pins.created_at',
            'transfer_at' => 'latest_transfer.transfer_at',
        ];

        $latestTransfer = DB::table('pin_transfers')
            ->select('pin_id')
            ->selectRaw('MAX(created_at) as transfer_at')
            ->groupBy('pin_id');

        $query = ModelPin::query()
            ->join('products', 'pins.product_id', '=', 'products.id')
            ->leftJoin('product_varians', 'pins.varian_id', '=', 'product_varians.id')
            ->leftJoin('member_networks as stockist_network', 'pins.owner_id', '=', 'stockist_network.id')
            ->leftJoin('members as stockist_member', 'stockist_network.member_id', '=', 'stockist_member.id')
            ->leftJoinSub($latestTransfer, 'latest_transfer', function ($join) {
                $join->on('pins.id', '=', 'latest_transfer.pin_id');
            })
            ->where('pins.member_network_id', $networkId)
            ->select([
                'pins.id',
                'pins.id_pin',
                'stockist_member.username as stockist_username',
                'products.name as product_name',
                'product_varians.varian as varian_name',
                'pins.status',
                'pins.created_at as created_ad',
                'latest_transfer.transfer_at',
            ]);

        $query = $this->filters($query, $request, [
            'text' => [
                'id_pin' => 'pins.id_pin',
                'stockist_username' => 'stockist_member.username',
                'product_name' => 'products.name',
                'varian_name' => 'product_varians.varian',
            ],
            'select' => [
                'status' => 'pins.status',
            ],
            'date' => [
                'created_ad' => 'pins.created_at',
                'transfer_at' => 'latest_transfer.transfer_at',
            ],
        ]);

        $defaultSort = [
            'created_ad' => 'desc',
        ];
        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            $defaultSort,
            tieBreaker: ['pins.id' => 'desc']
        );
        $product = $this->paginate($query, $request);

        return [
            'product' => $product,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
