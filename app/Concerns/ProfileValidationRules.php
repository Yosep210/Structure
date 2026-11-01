<?php

namespace App\Concerns;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate member profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $memberId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'username' => $this->usernameRules($memberId),
            'email' => $this->emailRules($memberId),
        ];
    }

    /**
     * Get the validation rules used to validate member names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate member usernames.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?int $memberId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            $memberId === null
                ? Rule::unique(ModelMember::class)
                : Rule::unique(ModelMember::class)->ignore($memberId),
        ];
    }

    /**
     * Get the validation rules used to validate member emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $memberId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $memberId === null
                ? Rule::unique(ModelMember::class)
                : Rule::unique(ModelMember::class)->ignore($memberId),
        ];
    }
}
