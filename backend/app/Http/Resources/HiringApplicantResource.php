<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HiringApplicantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $worker = $this->worker;

        return [
            'id' => $this->id,
            'name' => $worker?->name,
            'email' => $worker?->email,
            'phone' => $worker?->phone,
            'level' => $worker ? $this->levelLabel((int) ($worker->completed_tasks_count ?? 0)) : 'Worker',
            'rating' => $worker ? round((float) ($worker->rating_avg ?? 0), 1) : null,
            'completed_tasks' => (int) ($worker->completed_tasks_count ?? 0),
            'status' => $this->status,
            'proposal' => $this->proposal,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function levelLabel(int $completedTasks): string
    {
        return match (true) {
            $completedTasks >= 50 => 'Diamond Worker',
            $completedTasks >= 30 => 'Platinum Worker',
            $completedTasks >= 15 => 'Gold Worker',
            $completedTasks >= 5 => 'Silver Worker',
            default => 'Bronze Worker',
        };
    }
}