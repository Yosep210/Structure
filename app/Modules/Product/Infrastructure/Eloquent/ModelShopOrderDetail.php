<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $shop_order_id
 * @property int|null $member_network_id
 * @property int|null $product_package_id
 * @property int|null $product_id
 * @property int|null $varian_id
 * @property string|null $type
 * @property numeric $weight
 * @property numeric $point
 * @property numeric $bv
 * @property numeric $omzet
 * @property numeric $price
 * @property numeric $price_cart
 * @property numeric $additional_cost
 * @property int $qty
 * @property numeric $discount
 * @property numeric $subtotal
 * @property numeric $subtotal_bv
 * @property numeric $subtotal_omzet
 * @property numeric $subtotal_weight
 * @property numeric $subtotal_cost
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelNetwork|null $network
 * @property-read ModelProductPackage|null $package
 * @property-read ModelProduct|null $product
 * @property-read ModelShopOrder $shopOrder
 * @property-read ModelProductVarian|null $varian
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereAdditionalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail wherePriceCart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereProductPackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereShopOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereSubtotalBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereSubtotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereSubtotalOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereSubtotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereVarianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrderDetail whereWeight($value)
 *
 * @mixin \Eloquent
 */
class ModelShopOrderDetail extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_order_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_order_id',
        'member_network_id',
        'product_package_id',
        'product_id',
        'varian_id',
        'type',
        'weight',
        'point',
        'bv',
        'omzet',
        'price',
        'price_cart',
        'additional_cost',
        'qty',
        'discount',
        'subtotal',
        'subtotal_bv',
        'subtotal_omzet',
        'subtotal_weight',
        'subtotal_cost',
    ];

    /**
     * Relationship to the shop order.
     */
    public function shopOrder(): BelongsTo
    {
        return $this->belongsTo(ModelShopOrder::class, 'shop_order_id');
    }

    /**
     * Relationship to the member network.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }

    /**
     * Relationship to the product package.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(ModelProductPackage::class, 'product_package_id');
    }

    /**
     * Relationship to the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ModelProduct::class, 'product_id');
    }

    /**
     * Relationship to the product variant.
     */
    public function varian(): BelongsTo
    {
        return $this->belongsTo(ModelProductVarian::class, 'varian_id');
    }
}
