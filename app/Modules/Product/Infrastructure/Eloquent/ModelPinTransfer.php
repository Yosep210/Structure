<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use App\Modules\Product\Domain\Enums\PinTransfer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $sender_id
 * @property int $member_network_id
 * @property int $pin_id
 * @property int $product_id
 * @property int|null $varian_id
 * @property int|null $product_package_id
 * @property numeric $amount
 * @property numeric $price_member
 * @property PinTransfer|null $type
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelProductPackage|null $package
 * @property-read ModelPin $pin
 * @property-read ModelProduct $product
 * @property-read ModelNetwork $recipient
 * @property-read ModelNetwork $sender
 * @property-read ModelProductVarian|null $varian
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer wherePinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer wherePriceMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereProductPackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPinTransfer whereVarianId($value)
 *
 * @mixin \Eloquent
 */
class ModelPinTransfer extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pin_transfers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'member_network_id',
        'pin_id',
        'product_id',
        'varian_id',
        'product_package_id',
        'amount',
        'price_member',
        'type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'sender_id' => 'integer',
            'member_network_id' => 'integer',
            'pin_id' => 'integer',
            'product_id' => 'integer',
            'varian_id' => 'integer',
            'product_package_id' => 'integer',
            'amount' => 'decimal:2',
            'price_member' => 'decimal:2',
            'type' => PinTransfer::class,
        ];
    }

    /**
     * Relationship to the sender member network.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'sender_id');
    }

    /**
     * Relationship to the recipient member network.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }

    /**
     * Relationship to the transferred PIN.
     */
    public function pin(): BelongsTo
    {
        return $this->belongsTo(ModelPin::class, 'pin_id');
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

    /**
     * Relationship to the product package.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(ModelProductPackage::class, 'product_package_id');
    }
}
