<?php

namespace App\Modules\Member\Infrastructure\Eloquent;

use App\Modules\Member\Domain\Enums\StatusMember;
use App\Notifications\ResetPasswordNotification;
use Carbon\CarbonImmutable;
use Database\Factories\ModelMemberFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property StatusMember $status
 * @property numeric $ewallet_balance
 * @property CarbonImmutable|null $last_login
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property-read ModelNetwork|null $network
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read ModelProfile $profile
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, Permission> $teams
 * @property-read int|null $teams_count
 *
 * @method static \Database\Factories\ModelMemberFactory factory($count = null, $state = [])
 * @method static Builder<static>|ModelMember newModelQuery()
 * @method static Builder<static>|ModelMember newQuery()
 * @method static Builder<static>|ModelMember onlyMembers()
 * @method static Builder<static>|ModelMember permission($permissions, bool $without = false)
 * @method static Builder<static>|ModelMember query()
 * @method static Builder<static>|ModelMember role($roles, ?string $guard = null, bool $without = false)
 * @method static Builder<static>|ModelMember team($teams, bool $without = false)
 * @method static Builder<static>|ModelMember whereCreatedAt($value)
 * @method static Builder<static>|ModelMember whereEmail($value)
 * @method static Builder<static>|ModelMember whereEmailVerifiedAt($value)
 * @method static Builder<static>|ModelMember whereEwalletBalance($value)
 * @method static Builder<static>|ModelMember whereId($value)
 * @method static Builder<static>|ModelMember whereLastLogin($value)
 * @method static Builder<static>|ModelMember whereName($value)
 * @method static Builder<static>|ModelMember wherePassword($value)
 * @method static Builder<static>|ModelMember whereRememberToken($value)
 * @method static Builder<static>|ModelMember whereStatus($value)
 * @method static Builder<static>|ModelMember whereTwoFactorConfirmedAt($value)
 * @method static Builder<static>|ModelMember whereTwoFactorRecoveryCodes($value)
 * @method static Builder<static>|ModelMember whereTwoFactorSecret($value)
 * @method static Builder<static>|ModelMember whereUpdatedAt($value)
 * @method static Builder<static>|ModelMember whereUsername($value)
 * @method static Builder<static>|ModelMember withoutPermission($permissions)
 * @method static Builder<static>|ModelMember withoutRole($roles, ?string $guard = null)
 * @method static Builder<static>|ModelMember withoutTeam($teams)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'username', 'email', 'email_verified_at', 'password', 'status', 'ewallet_balance', 'last_login'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class ModelMember extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $table = 'members';

    public function getEmailForPasswordReset()
    {
        return $this->username;
    }

    protected static function newFactory(): ModelMemberFactory
    {
        return ModelMemberFactory::new();
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkeys::passkeyModel(), 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'status' => StatusMember::class,
            'ewallet_balance' => 'decimal:2',
            'last_login' => 'datetime',
        ];
    }

    /**
     * Get the member's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ModelProfile::class, 'member_id')->withDefault();
    }

    public function network(): HasOne
    {
        return $this->hasOne(ModelNetwork::class, 'member_id');
    }

    /**
     * Scope a query to only include members (excluding admin).
     */
    public function scopeOnlyMembers(Builder $member): Builder
    {
        return $member->whereNotIn(
            'members.id',
            function ($subQuery) {
                $subQuery->select('model_id')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'admin')
                    ->where('model_has_roles.model_type', (new self)->getMorphClass());
            }
        );
    }

    // public function isActive(): bool
    // {
    //     return $this->status === StatusMember::ACTIVE;
    // }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token, $this->username));
    }
}
