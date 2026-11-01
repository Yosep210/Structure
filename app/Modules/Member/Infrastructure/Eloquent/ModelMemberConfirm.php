<?php

namespace App\Modules\Member\Infrastructure\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_id
 * @property int|null $downline_id
 * @property int|null $sponsor_id
 * @property int|null $parent_id
 * @property string|null $position
 * @property string $package
 * @property int $package_count
 * @property string|null $referral_code
 * @property numeric $bv
 * @property numeric $nominal
 * @property numeric $shipping
 * @property int|null $uniquecode
 * @property numeric $total_payment
 * @property int|null $shop_order_id
 * @property string|null $access
 * @property int $status
 * @property CarbonImmutable|null $dateconfirmed
 * @property string|null $confirm_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelNetwork|null $network
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereBv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereConfirmBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereDateconfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereDownlineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm wherePackageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereReferralCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereShopOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereSponsorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereTotalPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereUniquecode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelMemberConfirm whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelMemberConfirm extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member_confirms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'downline_id',
        'sponsor_id',
        'parent_id',
        'position',
        'package',
        'package_count',
        'referral_code',
        'bv',
        'nominal',
        'shipping',
        'uniquecode',
        'total_payment',
        'shop_order_id',
        'access',
        'status',
        'dateconfirmed',
        'confirm_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pairing_left' => 'decimal:2',
            'pairing_right' => 'decimal:2',
            'pairing_qualified' => 'decimal:2',
            'reward_left' => 'decimal:2',
            'reward_right' => 'decimal:2',
            'total_omzet' => 'decimal:2',
            'total_ro_omzet' => 'decimal:2',
            'total_ro_point' => 'decimal:2',
            'last_ro_omzet' => 'decimal:2',
            'last_ro_point' => 'decimal:2',
            'dateconfirmed' => 'datetime',
            'last_ro_date' => 'datetime',
        ];
    }

    /**
     * Relationship to the network HU node.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(ModelNetwork::class, 'member_network_id');
    }
}
