<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Product\Domain\Enums\PinStatus;
use App\Modules\Product\Infrastructure\Eloquent\ModelPin;
use Illuminate\Http\Request;

class GetUsageProductQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?int $networkId = null): array
    {
        $request ??= new Request;

        $sortColumns = [
            'id_pin' => 'pins.id_pin',
            'product_name' => 'product.name',
            'varian_name' => 'varian.varian',
            'stockist_username' => 'stockist_member.username',
            'stockist_name' => 'stockist_member.name',
            'receiver_username' => 'receiver_member.username',
            'receiver_name' => 'receiver_member.name',
            'type' => 'pins.type',
            'dateused' => 'pins.dateused',
            'created_at' => 'pins.created_at',
        ];

        $query = ModelPin::query()
            ->join('products as product', 'pins.product_id', '=', 'product.id')
            ->leftJoin('product_varians as varian', 'pins.varian_id', '=', 'varian.id')
            ->leftJoin('member_networks as stockist_net', 'pins.member_network_id', '=', 'stockist_net.id')
            ->leftJoin('members as stockist_member', 'stockist_net.member_id', '=', 'stockist_member.id')
            ->leftJoin('member_networks as receiver_net', 'pins.registered_network_id', '=', 'receiver_net.id')
            ->leftJoin('members as receiver_member', 'receiver_net.member_id', '=', 'receiver_member.id')
            ->where('pins.status', PinStatus::USED)
            ->whereNotNull('pins.registered_network_id')
            ->select([
                'pins.id',
                'pins.id_pin',
                'pins.type',
                'pins.dateused',
                'pins.created_at',
                'product.name as product_name',
                'varian.varian as varian_name',
                'stockist_member.username as stockist_username',
                'stockist_member.name as stockist_name',
                'receiver_member.username as receiver_username',
                'receiver_member.name as receiver_name',
            ]);

        if ($networkId !== null) {
            $query->where('pins.member_network_id', $networkId);
        }

        $query = $this->filters($query, $request, [
            'text' => [
                'id_pin' => 'pins.id_pin',
                'product_name' => 'product.name',
                'varian_name' => 'varian.varian',
                'stockist_username' => 'stockist_member.username',
                'receiver_username' => 'receiver_member.username',
                'receiver_name' => 'receiver_member.name',
            ],
            'select' => [
                'type' => 'pins.type',
            ],
            'date' => [
                'dateused' => 'pins.dateused',
            ],
        ]);

        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            'dateused',
            'desc',
        );
        $usage = $this->paginate($query, $request);

        return [
            'usage' => $usage,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
