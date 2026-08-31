<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'platform_commission' => ['required', 'integer', 'min:0', 'max:50'],
            'auto_approve_jobs' => ['required', 'boolean'],
            'maintenance_mode' => ['required', 'boolean'],
            'email_notifications' => ['required', 'boolean'],
        ];
    }
}
