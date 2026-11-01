<?php

namespace App\Modules\Product\Application\Queries;

use App\Concerns\Table\Filters;
use App\Concerns\Table\Paginates;
use App\Concerns\Table\Sorts;
use App\Modules\Product\Infrastructure\Eloquent\ModelPinTransfer;
use Illuminate\Http\Request;

class GetHistorySendProductQuery
{
    use Filters, Paginates, Sorts;

    public function __construct(
        //
    ) {}

    public function execute(?Request $request = null, ?int $networkId = null): array
    {
        $request ??= new Request;

        $sortColumns = [
            'sender_username' => 'sender_member.username',
            'recipient_username' => 'recipient_member.username',
            'product_name' => 'product.name',
            'type' => 'pin_transfers.type',
            'description' => 'pin_transfers.description',
            'created_at' => 'pin_transfers.created_at',
        ];

        $query = ModelPinTransfer::query()
            ->join('products as product', 'pin_transfers.product_id', '=', 'product.id')
            ->leftJoin('product_varians as varian', 'pin_transfers.varian_id', '=', 'varian.id')
            ->leftJoin('member_networks as sender_net', 'pin_transfers.sender_id', '=', 'sender_net.id')
            ->leftJoin('members as sender_member', 'sender_net.member_id', '=', 'sender_member.id')
            ->leftJoin('member_networks as recipient_net', 'pin_transfers.member_network_id', '=', 'recipient_net.id')
            ->leftJoin('members as recipient_member', 'recipient_net.member_id', '=', 'recipient_member.id')
            ->select([
                'pin_transfers.type',
                'pin_transfers.description',
                'pin_transfers.created_at',
                'product.name as product_name',
                'varian.varian as varian_name',
                'sender_member.username as sender_username',
                'sender_member.name as sender_name',
                'recipient_member.username as recipient_username',
                'recipient_member.name as recipient_name',
            ])
            ->selectRaw('MIN(pin_transfers.id) as id')
            ->selectRaw('COUNT(pin_transfers.id) as qty')
            ->groupBy(
                'pin_transfers.sender_id',
                'pin_transfers.member_network_id',
                'pin_transfers.created_at',
                'pin_transfers.product_id',
                'pin_transfers.varian_id',
                'pin_transfers.type',
                'pin_transfers.description',
                'product.name',
                'varian.varian',
                'sender_member.username',
                'sender_member.name',
                'recipient_member.username',
                'recipient_member.name'
            );

        if ($networkId !== null) {
            $query->where(function ($query) use ($networkId) {
                $query->where('pin_transfers.sender_id', $networkId)
                    ->orWhere('pin_transfers.member_network_id', $networkId);
            });
        }

        $query = $this->filters($query, $request, [
            'text' => [
                'sender_username' => 'sender_member.username',
                'recipient_username' => 'recipient_member.username',
                'product_name' => 'product.name',
                'description' => 'pin_transfers.description',
            ],
            'select' => [
                'type' => 'pin_transfers.type',
            ],
            'date' => [
                'created_at' => 'pin_transfers.created_at',
            ],
        ]);

        $defaultSort = [
            'created_at' => 'desc',
        ];
        ['sort' => $sort, 'direction' => $direction] = $this->sort(
            $query,
            $request,
            $sortColumns,
            $defaultSort,
            tieBreaker: [
                'id' => 'desc',
            ]
        );
        $history = $this->paginate($query, $request);

        return [
            'history' => $history,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
