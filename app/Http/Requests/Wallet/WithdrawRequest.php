<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:999999999',
            ],
            'idempotency_key' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
                'regex:/^[a-zA-Z0-9_\-\.]+$/', // Only alphanumeric, underscore, hyphen, dot
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Withdrawal amount is required',
            'amount.integer' => 'Amount must be specified in cents as a whole number',
            'amount.min' => 'Minimum withdrawal amount is 1 cent',
            'amount.max' => 'Withdrawal amount exceeds the maximum limit of $9,999,999.99',
            'idempotency_key.min' => 'Idempotency key cannot be empty',
            'idempotency_key.max' => 'Idempotency key is too long (maximum 255 characters)',
            'idempotency_key.regex' => 'Idempotency key can only contain letters, numbers, underscores, hyphens, and dots',
        ];
    }
}
