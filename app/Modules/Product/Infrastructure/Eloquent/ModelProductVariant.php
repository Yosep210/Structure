<?php

namespace App\Modules\Product\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property GeneralStatus $status
 * @property-read ModelProduct|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProductVariant query()
 *
 * @mixin \Eloquent
 */
class ModelProductVariant extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_variants';

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

    protected function casts(): array
    {
        return [
            'status' => GeneralStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ModelProduct::class, 'product_id');
    }
}
