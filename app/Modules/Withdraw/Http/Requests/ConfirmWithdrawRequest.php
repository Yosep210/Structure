<?php

namespace App\Modules\Withdraw\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmWithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
