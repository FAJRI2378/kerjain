<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('status', Task::STATUS_APPROVED)
            ->with(['owner', 'category'])
            ->withCount('applicants');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%"));
            });
        }

        if ($category = $request->get('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        $perPage = max(1, min((int) $request->get('per_page', 6), 50));

        $tasks = $query->orderByDesc('created_at')->paginate($perPage);

        return TaskResource::collection($tasks);
    }
}