<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'freelancer';
    }

    public function rules(): array
    {
        return [
            'proof_url' => ['required', 'url', 'max:2048'],
        ];
    }
}
