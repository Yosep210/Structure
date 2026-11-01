<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Product\Domain\Enums\ProductType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $slug
 * @property ProductType|null $type
 * @property int $hu
 * @property numeric $bv
 * @property numeric $price_hpp
 * @property numeric $price
 * @property numeric $price_member
 * @property int $stock
 * @property numeric $price_customer
 * @property numeric $sponsor_point
 * @property numeric $pairing_point
 * @property numeric $reward_point
 * @property numeric $stockist_point
 * @property numeric $reward_budget
 * @property numeric $weight
 * @property string|null $description
 * @property string|null $image
 * @property bool $s3_upload
 * @property bool $show_order
 * @property GeneralStatus $status
 * @property string|null $created_by
 * @property string|null $modified_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, ModelProductVarian> $varians
 * @property-read int|null $varians_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereHu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct wherePairingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct wherePriceCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct wherePriceHpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct wherePriceMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereRewardBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereRewardPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereS3Upload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereShowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereSponsorPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereStockistPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProduct whereWeight($value)
 *
 * @mixin \Eloquent
 */
class ModelProduct extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

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
        'bv',
        'price_hpp',
        'price',
        'price_member',
        'stock',
        'price_customer',
        'sponsor_point',
        'pairing_point',
        'reward_point',
        'stockist_point',
        'reward_budget',
        'weight',
        'description',
        'image',
        's3_upload',
        'show_order',
        'status',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'status' => GeneralStatus::class,
            'hu' => 'integer',
            'bv' => 'decimal:2',
            'price_hpp' => 'decimal:2',
            'price' => 'decimal:2',
            'price_member' => 'decimal:2',
            'stock' => 'integer',
            'price_customer' => 'decimal:2',
            'sponsor_point' => 'decimal:2',
            'pairing_point' => 'decimal:2',
            'reward_point' => 'decimal:2',
            'stockist_point' => 'decimal:2',
            'reward_budget' => 'decimal:2',
            'weight' => 'decimal:2',
            's3_upload' => 'boolean',
            'show_order' => 'boolean',
        ];
    }

    public function varians(): HasMany
    {
        return $this->hasMany(ModelProductVarian::class, 'product_id');
    }
}
