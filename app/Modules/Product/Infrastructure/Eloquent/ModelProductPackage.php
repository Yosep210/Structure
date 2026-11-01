<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\GeneralStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $slug
 * @property string|null $type
 * @property int $hu
 * @property int $type_price
 * @property int $total_item
 * @property int $total_qty
 * @property numeric $total_amount
 * @property numeric $bv
 * @property numeric $price
 * @property numeric $weight
 * @property numeric $sponsor_point
 * @property numeric $pairing_point
 * @property numeric $stockist_point
 * @property numeric $reward_point
 * @property string|null $image
 * @property string|null $description
 * @property bool $show_order
 * @property GeneralStatus $status
 * @property string|null $created_by
 * @property string|null $modified_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereHu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage wherePairingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereRewardPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereShowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereSponsorPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereStockistPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereTotalItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereTypePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductPackage whereWeight($value)
 *
 * @mixin \Eloquent
 */
class ModelProductPackage extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_packages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'slug',
        'type',
        'hu',
        'type_price',
        'total_item',
        'total_qty',
        'total_amount',
        'bv',
        'price',
        'weight',
        'sponsor_point',
        'pairing_point',
        'stockist_point',
        'reward_point',
        'image',
        'description',
        'show_order',
        'status',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'hu' => 'integer',
            'type_price' => 'integer',
            'total_item' => 'integer',
            'total_qty' => 'integer',
            'total_amount' => 'decimal:2',
            'bv' => 'decimal:2',
            'price' => 'decimal:2',
            'weight' => 'decimal:2',
            'sponsor_point' => 'decimal:2',
            'pairing_point' => 'decimal:2',
            'stockist_point' => 'decimal:2',
            'reward_point' => 'decimal:2',
            'show_order' => 'boolean',
            'status' => GeneralStatus::class,
        ];
    }

    /**
     * Relationship to package items.
     */
    // public function items(): HasMany
    // {
    //     return $this->hasMany(ProductPackageItem::class, 'package_id');
    // }

    /**
     * Relationship to area fees.
     */
    // public function areaFees(): MorphMany
    // {
    //     return $this->morphMany(ProductAreaFee::class, 'feeable');
    // }
}
