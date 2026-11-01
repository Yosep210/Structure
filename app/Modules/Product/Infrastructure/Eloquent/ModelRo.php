<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use App\Modules\Product\Domain\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $invoice
 * @property int $activator_id
 * @property int $member_network_id
 * @property int|null $stockist_id
 * @property int $shop_order_id
 * @property string|null $type
 * @property array<array-key, mixed>|null $pins
 * @property array<array-key, mixed>|null $meta
 * @property OrderStatus $status
 * @property numeric $point
 * @property numeric $total_bv
 * @property int $total_qty
 * @property numeric $subtotal
 * @property int|null $unique
 * @property numeric $shipping
 * @property numeric $shipping_discount
 * @property numeric $discount
 * @property string|null $voucher
 * @property numeric $fee
 * @property numeric $ppn
 * @property bool $autoro
 * @property numeric $total_checkout
 * @property numeric $total_payment
 * @property numeric $total_omzet
 * @property string|null $shipping_method
 * @property string|null $description
 * @property CarbonImmutable|null $dateconfirmed
 * @property string|null $confirmed_by
 * @property string|null $modified_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelNetwork $activator
 * @property-read ModelNetwork $network
 * @property-read ModelShopOrder $shopOrder
 * @property-read ModelNetwork|null $stockist
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereActivatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereAutoro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereDateconfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo wherePins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereShippingDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereShopOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereStockistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereTotalBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereTotalCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereTotalOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereTotalPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelRo whereVoucher($value)
 *
 * @mixin \Eloquent
 */
class ModelRo extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ros';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice',
        'activator_id',
        'member_network_id',
        'stockist_id',
        'shop_order_id',
        'type',
        'pins',
        'meta',
        'status',
        'point',
        'total_bv',
        'total_qty',
        'subtotal',
        'unique',
        'shipping',
        'shipping_discount',
        'discount',
        'voucher',
        'fee',
        'ppn',
        'autoro',
        'total_checkout',
        'total_payment',
        'total_omzet',
        'shipping_method',
        'description',
        'dateconfirmed',
        'confirmed_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'activator_id' => 'integer',
            'member_network_id' => 'integer',
            'stockist_id' => 'integer',
            'shop_order_id' => 'integer',
            'pins' => 'array',
            'meta' => 'array',
            'status' => OrderStatus::class,
            'point' => 'decimal:2',
            'total_bv' => 'decimal:2',
            'total_qty' => 'integer',
            'subtotal' => 'decimal:2',
            'unique' => 'integer',
            'shipping' => 'decimal:2',
            'shipping_discount' => 'decimal:2',
            'discount' => 'decimal:2',
            'fee' => 'decimal:2',
            'ppn' => 'decimal:2',
            'autoro' => 'boolean',
            'total_checkout' => 'decimal:2',
            'total_payment' => 'decimal:2',
            'total_omzet' => 'decimal:2',
            'dateconfirmed' => 'datetime',
        ];
    }

    /**
     * Relationship to the activator member network.
     */
    public function activator(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'activator_id');
    }

    /**
     * Relationship to the target member network.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }

    /**
     * Relationship to the stockist member network.
     */
    public function stockist(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'stockist_id');
    }

    /**
     * Relationship to the shop order.
     */
    public function shopOrder(): BelongsTo
    {
        return $this->belongsTo(ModelShopOrder::class, 'shop_order_id');
    }
}
