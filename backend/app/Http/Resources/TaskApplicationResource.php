<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'worker' => new UserResource($this->whenLoaded('worker')),
            'status' => $this->status,
            'proposal' => $this->proposal,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
