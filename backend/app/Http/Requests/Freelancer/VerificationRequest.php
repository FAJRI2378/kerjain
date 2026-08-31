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
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d+$/'],
        ];
    }
}
