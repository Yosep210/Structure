<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the member's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(ModelMember $member, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $member->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
