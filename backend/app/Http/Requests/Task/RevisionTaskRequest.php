<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class RevisionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hirer';
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.max' => 'Catatan revisi maksimal 1000 karakter.',
        ];
    }
}