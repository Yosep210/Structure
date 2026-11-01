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
 * @property string|null $id_order_pin
 * @property string $id_pin
 * @property int $member_network_id
 * @property int $owner_id
 * @property int|null $registrar_id
 * @property int|null $registered_network_id
 * @property int $product_id
 * @property int|null $varian_id
 * @property string|null $type
 * @property numeric $bv
 * @property numeric $amount
 * @property int $status
 * @property bool $used
 * @property string|null $description
 * @property CarbonImmutable|null $dateexpired
 * @property CarbonImmutable|null $dateused
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelNetwork $holder
 * @property-read ModelNetwork $owner
 * @property-read ModelProduct $product
 * @property-read ModelProductVarian|null $variant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereDateexpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereDateused($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereIdOrderPin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereIdPin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereRegisteredNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereRegistrarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelPin whereVarianId($value)
 *
 * @mixin \Eloquent
 */
class ModelPin extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_order_pin',
        'id_pin',
        'member_network_id',
        'owner_id',
        'registrar_id',
        'registered_network_id',
        'product_id',
        'varian_id',
        'type',
        'bv',
        'amount',
        'status',
        'used',
        'description',
        'dateexpired',
        'dateused',
    ];

    protected function casts(): array
    {
        return [
            'bv' => 'decimal:2',
            'amount' => 'decimal:2',
            'status' => 'integer',
            'used' => 'boolean',
            'dateexpired' => 'datetime',
            'dateused' => 'datetime',
        ];
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'owner_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ModelProduct::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ModelProductVarian::class, 'varian_id');
    }
}
