<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'budget' => $this->budget,
            'location' => $this->location,
            'status' => $this->status,
            'proof_url' => $this->proof_url,
            'deadline' => $this->deadline,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'worker' => $this->whenLoaded('worker', fn () => $this->worker ? new UserResource($this->worker) : null),
            'category' => $this->whenLoaded('category', fn () => new JobCategoryResource($this->category)),
            'applicants_count' => $this->whenCounted('applicants'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
