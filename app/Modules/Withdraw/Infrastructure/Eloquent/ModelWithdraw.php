<?php

namespace App\Modules\Withdraw\Infrastructure\Eloquent;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Withdraw\Domain\Enums\WithdrawStatus;
use App\Modules\Withdraw\Domain\Enums\WithdrawType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $member_id
 * @property WithdrawType|null $type
 * @property int|null $bank_id
 * @property string|null $bank_type
 * @property string|null $bank_code
 * @property string|null $bank_name
 * @property string|null $bank_account
 * @property string|null $bank_account_name
 * @property numeric $nominal
 * @property numeric $nominal_receipt
 * @property numeric $tax
 * @property numeric $auto_ro
 * @property numeric $admin_fund
 * @property WithdrawStatus $status
 * @property string|null $flip_id
 * @property string|null $linkita_inquiry
 * @property string|null $inquiry_status
 * @property string|null $linkita_pay
 * @property string|null $payment_status
 * @property CarbonImmutable|null $dateconfirmed
 * @property string|null $confirm_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ModelMember $member
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereAdminFund($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereAutoRo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereBankType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereConfirmBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereDateconfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereFlipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereInquiryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereLinkitaInquiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereLinkitaPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereNominalReceipt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelWithdraw whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModelWithdraw extends Model
{
    use HasFactory;
    // use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'withdrawals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'type',
        'bank_id',
        'bank_type',
        'bank_code',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'nominal',
        'nominal_receipt',
        'tax',
        'auto_ro',
        'admin_fund',
        'status',
        'flip_id',
        'linkita_inquiry',
        'inquiry_status',
        'linkita_pay',
        'payment_status',
        'dateconfirmed',
        'confirm_by',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => WithdrawType::class,
            'status' => WithdrawStatus::class,
            'nominal' => 'decimal:2',
            'nominal_receipt' => 'decimal:2',
            'tax' => 'decimal:2',
            'auto_ro' => 'decimal:2',
            'admin_fund' => 'decimal:2',
            'dateconfirmed' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ModelMember::class, 'member_id');
    }
}
