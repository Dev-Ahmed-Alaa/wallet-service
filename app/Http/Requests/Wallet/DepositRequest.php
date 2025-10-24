<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
                'min:1', // Minimum 1 cent
                'max:999999999', // Maximum ~$9.9M (reasonable limit for deposits)
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
            'amount.required' => 'Deposit amount is required',
            'amount.integer' => 'Amount must be specified in cents as a whole number',
            'amount.min' => 'Minimum deposit amount is 1 cent',
            'amount.max' => 'Deposit amount exceeds the maximum limit of $9,999,999.99',
            'idempotency_key.min' => 'Idempotency key cannot be empty',
            'idempotency_key.max' => 'Idempotency key is too long (maximum 255 characters)',
            'idempotency_key.regex' => 'Idempotency key can only contain letters, numbers, underscores, hyphens, and dots',
        ];
    }
}
