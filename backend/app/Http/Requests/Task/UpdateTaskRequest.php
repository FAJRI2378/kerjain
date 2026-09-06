<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hirer';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'exists:job_categories,slug'],
            'location' => ['required', 'string', 'max:255'],
            'budget' => ['required', 'integer', 'min:50000'],
            'description' => ['required', 'string'],
            'deadline' => ['nullable', 'string', 'max:50'],
        ];
    }
}