<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'business_profile' => $this->whenLoaded('businessProfile', fn () => new BusinessProfileResource($this->businessProfile)),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
