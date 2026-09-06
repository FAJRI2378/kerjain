<?php

namespace App\Http\Requests\Task;

use Illuminate\Contracts\Validation\Validator;
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
            'proof_url' => ['nullable', 'url', 'max:2048'],
            'proof' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->filled('proof_url') && !$this->hasFile('proof')) {
                $validator->errors()->add('proof', 'Bukti pekerjaan wajib diisi minimal satu (link atau gambar).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'proof_url.url' => 'Link bukti harus berupa URL yang valid.',
            'proof_url.max' => 'Link bukti maksimal 2048 karakter.',
            'proof.image' => 'File bukti harus berupa gambar.',
            'proof.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp.',
            'proof.max' => 'Ukuran gambar bukti maksimal 2 MB.',
        ];
    }
}