<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_user_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // Cannot transfer to self
                    if ($value == $this->user()->id) {
                        $fail('You cannot transfer money to yourself.');
                    }
                },
            ],
            'amount' => [
                'required',
                'integer',
                'min:1', // Minimum 1 cent
                'max:999999999', // Maximum ~$9.9M (reasonable limit for transfers)
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
            'to_user_id.required' => 'Recipient user ID is required',
            'to_user_id.integer' => 'Recipient user ID must be a valid number',
            'to_user_id.exists' => 'Recipient user does not exist',
            'amount.required' => 'Transfer amount is required',
            'amount.integer' => 'Amount must be specified in cents as a whole number',
            'amount.min' => 'Minimum transfer amount is 1 cent',
            'amount.max' => 'Transfer amount exceeds the maximum limit of $9,999,999.99',
            'idempotency_key.min' => 'Idempotency key cannot be empty',
            'idempotency_key.max' => 'Idempotency key is too long (maximum 255 characters)',
            'idempotency_key.regex' => 'Idempotency key can only contain letters, numbers, underscores, hyphens, and dots',
        ];
    }
}
