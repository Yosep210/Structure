<?php

namespace App\Modules\EWallet\Infrastructure\Eloquent;

use App\Modules\EWallet\Domain\Enums\EntryType;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Withdraw\Domain\Enums\TypeWithdraw;
use App\Modules\Withdraw\Domain\Enums\WithdrawCategory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_id
 * @property TypeWithdraw $source
 * @property int $source_id
 * @property WithdrawCategory $category
 * @property numeric $nominal
 * @property numeric $percent
 * @property numeric $autoro
 * @property numeric $tax
 * @property numeric $amount
 * @property EntryType $type
 * @property GeneralStatus $status
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereAutoro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelEWallet whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelEWallet extends Model
{
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ewallets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'source',
        'source_id',
        'category',
        'nominal',
        'percent',
        'autoro',
        'tax',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'source' => TypeWithdraw::class,
            'type' => EntryType::class,
            'category' => WithdrawCategory::class,
            'nominal' => 'decimal:2',
            'percent' => 'decimal:2',
            'autoro' => 'decimal:2',
            'tax' => 'decimal:2',
            'amount' => 'decimal:2',
            'status' => GeneralStatus::class,
        ];
    }
}
