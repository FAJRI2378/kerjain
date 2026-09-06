<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'role' => $this->role,
            'status' => $this->status,
            'business_name' => $data['business_name'] ?? null,
            'category' => $data['category'] ?? ($data['business_type'] ?? null),
            'business_type' => $data['business_type'] ?? null,
            'address' => $data['address'] ?? null,
            'photo_url' => isset($data['photo_path'])
                ? Storage::disk('public')->url($data['photo_path'])
                : null,
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_holder_name' => $data['account_holder_name'] ?? null,
            'submitted_at' => $this->created_at?->toISOString(),
            'note' => $data['note'] ?? null,
            'admin_note' => $this->admin_note,
        ];
    }
}