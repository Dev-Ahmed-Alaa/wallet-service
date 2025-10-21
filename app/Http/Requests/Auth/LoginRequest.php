<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:1', // Allow any password length for login (security handled by hashing)
                'max:128',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.max' => 'Email address is too long',
            'password.required' => 'Password is required',
            'password.min' => 'Password cannot be empty',
            'password.max' => 'Password is too long',
        ];
    }
}
