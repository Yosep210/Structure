<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\GeneralStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $product_id
 * @property string $varian
 * @property numeric $bv
 * @property numeric $price_hpp
 * @property numeric $price
 * @property int $stock
 * @property numeric $weight
 * @property string|null $image
 * @property GeneralStatus $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian wherePriceHpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereVarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVarian whereWeight($value)
 *
 * @mixin \Eloquent
 */
class ModelProductVarian extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_varians';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'varian',
        'bv',
        'price_hpp',
        'price',
        'stock',
        'weight',
        'image',
        'status',
    ];

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    protected function casts(): array
    {
        return [
            'bv' => 'decimal:2',
            'price_hpp' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'weight' => 'decimal:2',
            'status' => GeneralStatus::class,
        ];
    }
}
