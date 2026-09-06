<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BusinessProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'category' => $this->category,
            'phone' => $this->phone,
            'address' => $this->address,
            'business_type' => $this->business_type,
            'store_photo' => $this->store_photo,
            'store_photo_url' => $this->store_photo ? Storage::disk('public')->url($this->store_photo) : null,
        ];
    }
}
