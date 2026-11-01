<?php

namespace App\Modules\Member\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\PositionMember;
use App\Modules\Member\Domain\Enums\RankMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_id
 * @property string $referral_code
 * @property int $hu_index
 * @property string $package
 * @property int|null $sponsor_id
 * @property int|null $parent_id
 * @property PositionMember|null $position
 * @property int $group
 * @property int $gen
 * @property int $level
 * @property string|null $tree
 * @property string|null $tree_sponsor
 * @property numeric $total_omzet
 * @property numeric $package_omzet
 * @property RankMember|null $rank_id
 * @property bool $is_ro
 * @property numeric $autoro_balance
 * @property CarbonImmutable|null $datero
 * @property CarbonImmutable|null $dateupgrade
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, ModelNetwork> $children
 * @property-read int|null $children_count
 * @property-read ModelMember $member
 * @property-read ModelNetwork|null $parent
 * @property-read ModelNetwork|null $sponsor
 * @property-read Collection<int, ModelNetwork> $sponsorees
 * @property-read int|null $sponsorees_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereAutoroBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereDatero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereDateupgrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereGen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereHuIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereIsRo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork wherePackageOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereRankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereReferralCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereSponsorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereTotalOmzet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereTree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereTreeSponsor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelNetwork whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelNetwork extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member_networks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'referral_code',
        'hu_index',
        'package',
        'sponsor_id',
        'parent_id',
        'position',
        'group',
        'gen',
        'level',
        'tree',
        'tree_sponsor',
        'total_omzet',
        'package_omzet',
        'rank_id',
        'is_ro',
        'autoro_balance',
        'datero',
        'dateupgrade',
    ];

    protected function casts(): array
    {
        return [
            'hu_index' => 'integer',
            'group' => 'integer',
            'gen' => 'integer',
            'level' => 'integer',
            'position' => PositionMember::class,
            'rank_id' => RankMember::class,
            'is_ro' => 'boolean',
            'total_omzet' => 'decimal:2',
            'package_omzet' => 'decimal:2',
            'autoro_balance' => 'decimal:2',
            'datero' => 'datetime',
            'dateupgrade' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ModelMember::class, 'member_id');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function sponsorees(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_id');
    }
}
