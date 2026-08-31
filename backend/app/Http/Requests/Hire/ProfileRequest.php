<?php

namespace App\Http\Requests\Hire;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hirer';
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}
