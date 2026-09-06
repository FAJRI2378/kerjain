<?php

namespace App\Http\Requests\Hire;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hirer';
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:10000'],
            'bank_name' => ['required', 'string', 'max:255'],
        ];
    }
}