<?php

namespace App\Modules\Bonus\Infrastructure\Eloquent;

use App\Modules\Bonus\Domain\Enums\BonusType;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_network_id
 * @property string $code
 * @property BonusType $type
 * @property string|null $description
 * @property numeric $amount
 * @property numeric $tax
 * @property numeric $amount_tax
 * @property GeneralStatus $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereAmountTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelBonus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelBonus extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bonuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_network_id',
        'code',
        'type',
        'description',
        'amount',
        'tax',
        'amount_tax',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => BonusType::class,
            'amount' => 'decimal:2',
            'tax' => 'decimal:2',
            'amount_tax' => 'decimal:2',
            'status' => GeneralStatus::class,
        ];
    }
}
