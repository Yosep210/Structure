<?php

namespace App\Modules\Member\Http\Requests;

use App\Modules\Member\Domain\Enums\StatusMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusMember::class)],
        ];
    }
}
