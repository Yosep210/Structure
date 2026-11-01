<?php

namespace App\Modules\Member\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\GenderEnum;
use App\Modules\Member\Domain\Enums\GeneralStatus;
use App\Modules\Withdraw\Domain\Enums\StatusWithdraw;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_id
 * @property CarbonImmutable|null $birthdate
 * @property GenderEnum|null $gender
 * @property string|null $phone
 * @property int|null $bank_id
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property GeneralStatus $bank_status
 * @property string|null $id_card_number
 * @property string|null $id_card_img
 * @property string|null $npwp_number
 * @property string|null $image
 * @property string|null $qr_code_img
 * @property int|null $country_id
 * @property int|null $province_id
 * @property int|null $city_id
 * @property int|null $district_id
 * @property string|null $village
 * @property string|null $address
 * @property string|null $password_security
 * @property string|null $password_transaction
 * @property bool $agree_ethic
 * @property StatusWithdraw $wd_status
 * @property numeric $wd_min
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelMember $member
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereAgreeEthic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereBankAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereBankAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereBankStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereBirthdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereIdCardImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereIdCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereNpwpNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile wherePasswordSecurity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile wherePasswordTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereQrCodeImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereVillage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereWdMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelProfile whereWdStatus($value)
 *
 * @mixin \Eloquent
 */
class ModelProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'birthdate',
        'gender',
        'phone',
        'bank_id',
        'bank_account_number',
        'bank_account_name',
        'bank_status',
        'id_card_number',
        'id_card_img',
        'npwp_number',
        'image',
        'qr_code_img',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
        'village',
        'address',
        'password_security',
        'password_transaction',
        'agree_ethic',
        'wd_status',
        'wd_min',
    ];

    protected $hidden = [
        'password_security',
        'password_transaction',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'gender' => GenderEnum::class,
            'bank_status' => GeneralStatus::class,
            'agree_ethic' => 'boolean',
            'wd_status' => StatusWithdraw::class,
            'wd_min' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ModelMember::class, 'member_id');
    }
}
