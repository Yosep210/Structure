<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use App\Modules\Product\Domain\Enums\OrderStatus;
use App\Modules\Product\Domain\Enums\OrderType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $invoice
 * @property int|null $member_network_id
 * @property int|null $stockist_id
 * @property OrderType $type_order
 * @property array<array-key, mixed>|null $products
 * @property array<array-key, mixed>|null $meta
 * @property OrderStatus $status
 * @property numeric $point_sponsor
 * @property numeric $point_pairing
 * @property numeric $point_reward
 * @property numeric $point_stockist
 * @property numeric $total_bv
 * @property numeric $total_omzet
 * @property int $total_qty
 * @property numeric $subtotal
 * @property numeric $shipping
 * @property int|null $unique
 * @property numeric $discount
 * @property numeric $shipping_discount
 * @property numeric $fee
 * @property numeric $ppn
 * @property numeric $handling_fee
 * @property numeric $insurance_fee
 * @property numeric $additional_cost
 * @property bool $autoro
 * @property numeric $total_checkout
 * @property numeric $total_payment
 * @property numeric $payment_remain
 * @property string|null $voucher
 * @property numeric $saldo_eproduct
 * @property numeric $saldo_eshipping
 * @property numeric $saldo_eshipping_subsidy
 * @property string|null $payment_method
 * @property string|null $payment_shipping_method
 * @property int|null $payment_shipping_status
 * @property string|null $bank_code
 * @property string|null $account_number
 * @property string|null $shipping_method
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property int|null $province_id
 * @property int|null $district_id
 * @property int|null $subdistrict_id
 * @property string|null $province
 * @property string|null $district
 * @property string|null $subdistrict
 * @property string|null $village
 * @property string $address
 * @property string|null $postcode
 * @property string|null $courier
 * @property string|null $service
 * @property numeric $weight
 * @property string|null $resi
 * @property string|null $pin_transfer
 * @property string|null $description
 * @property string|null $created_by
 * @property string|null $confirmed_by
 * @property string|null $modified_by
 * @property CarbonImmutable|null $dateconfirmed
 * @property CarbonImmutable|null $datestockistconfirmed
 * @property CarbonImmutable|null $dateexpired
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, ModelShopOrderDetail> $details
 * @property-read int|null $details_count
 * @property-read ModelNetwork|null $network
 * @property-read Collection<int, ModelRo> $ro
 * @property-read int|null $ro_count
 * @property-read ModelNetwork|null $stockist
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereAdditionalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereAutoro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereCourier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDateconfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDateexpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDatestockistconfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereHandlingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereInsuranceFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePaymentRemain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePaymentShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePaymentShippingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePinTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePointPairing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePointReward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePointSponsor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePointStockist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereResi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSaldoEproduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSaldoEshipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSaldoEshippingSubsidy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereShippingDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereStockistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSubdistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSubdistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTotalBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTotalCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTotalOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTotalPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereTypeOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereVillage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereVoucher($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelShopOrder whereWeight($value)
 *
 * @mixin \Eloquent
 */
class ModelShopOrder extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice',
        'member_network_id',
        'stockist_id',
        'type_order',
        'products',
        'meta',
        'status',
        'point_sponsor',
        'point_pairing',
        'point_reward',
        'point_stockist',
        'total_bv',
        'total_omzet',
        'total_qty',
        'subtotal',
        'shipping',
        'unique',
        'discount',
        'shipping_discount',
        'fee',
        'ppn',
        'handling_fee',
        'insurance_fee',
        'additional_cost',
        'autoro',
        'total_checkout',
        'total_payment',
        'payment_remain',
        'voucher',
        'saldo_eproduct',
        'saldo_eshipping',
        'saldo_eshipping_subsidy',
        'payment_method',
        'payment_shipping_method',
        'payment_shipping_status',
        'bank_code',
        'account_number',
        'shipping_method',
        'name',
        'phone',
        'email',
        'province_id',
        'district_id',
        'subdistrict_id',
        'province',
        'district',
        'subdistrict',
        'village',
        'address',
        'postcode',
        'courier',
        'service',
        'weight',
        'resi',
        'pin_transfer',
        'description',
        'created_by',
        'confirmed_by',
        'modified_by',
        'dateconfirmed',
        'datestockistconfirmed',
        'dateexpired',
    ];

    protected function casts(): array
    {
        return [

            'type_order' => OrderType::class,
            'meta' => 'array',
            'status' => OrderStatus::class,
            'point_sponsor' => 'decimal:2',
            'point_pairing' => 'decimal:2',
            'point_reward' => 'decimal:2',
            'point_stockist' => 'decimal:2',
            'total_bv' => 'decimal:2',
            'total_omzet' => 'decimal:2',
            'total_qty' => 'integer',
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'unique' => 'integer',
            'discount' => 'decimal:2',
            'shipping_discount' => 'decimal:2',
            'fee' => 'decimal:2',
            'ppn' => 'decimal:2',
            'handling_fee' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'additional_cost' => 'decimal:2',
            'autoro' => 'boolean',
            'total_checkout' => 'decimal:2',
            'total_payment' => 'decimal:2',
            'payment_remain' => 'decimal:2',
            'saldo_eproduct' => 'decimal:2',
            'saldo_eshipping' => 'decimal:2',
            'saldo_eshipping_subsidy' => 'decimal:2',
            'payment_shipping_status' => 'integer',
            'weight' => 'decimal:2',
            'dateconfirmed' => 'datetime',
            'datestockistconfirmed' => 'datetime',
            'dateexpired' => 'datetime',
            'products' => 'array',
        ];
    }

    /**
     * Relationship to the member network.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }

    /**
     * Relationship to the stockist network.
     */
    public function stockist(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'stockist_id');
    }

    /**
     * Relationship to shop order details.
     */
    public function details(): HasMany
    {
        return $this->hasMany(ModelShopOrderDetail::class, 'shop_order_id');
    }

    /**
     * Relationship to repeat orders (ro).
     */
    public function ro(): HasMany
    {
        return $this->hasMany(ModelRo::class, 'shop_order_id');
    }
}
