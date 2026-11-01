<?php

namespace App\Modules\AutoRo\Infrastructure\Eloquent;

use App\Modules\EWallet\Domain\Enums\EntryType;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Withdraw\Domain\Enums\TypeWithdraw;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_network_id
 * @property TypeWithdraw $source
 * @property int $source_id
 * @property numeric $nominal
 * @property numeric $percent
 * @property numeric $amount
 * @property EntryType $type
 * @property GeneralStatus $status
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereMemberNetworkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelAutoRo whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelAutoRo extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auto_ros';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_network_id',
        'source',
        'source_id',
        'nominal',
        'percent',
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
            'nominal' => 'decimal:2',
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
            'status' => GeneralStatus::class,
        ];
    }
}
