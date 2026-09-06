<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'freelancer';
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:40'],
            'account_holder_name' => ['required', 'string', 'max:100'],
        ];
    }
}