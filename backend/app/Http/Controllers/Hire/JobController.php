<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\JobCategory;
use App\Models\Setting;
use App\Models\Task;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function myJobs(Request $request)
    {
        $tasks = Task::where('owner_id', $request->user()->id)
            ->with(['category', 'worker'])
            ->withCount('applicants')
            ->orderByDesc('created_at')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $category = JobCategory::where('slug', $request->category)->firstOrFail();

        $autoApprove = filter_var(Setting::get('auto_approve_jobs', false), FILTER_VALIDATE_BOOLEAN);

        $task = Task::create([
            'owner_id' => $request->user()->id,
            'category_id' => $category->id,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'location' => $request->location,
            'deadline' => $request->get('deadline'),
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        return (new TaskResource($task->load(['owner', 'category'])))->response()->setStatusCode(201);
    }

    public function complete(Request $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($task->status !== 'reviewing') {
            return response()->json(['message' => 'Task is not awaiting approval.'], 409);
        }

        $task->update(['status' => 'completed']);

        return new TaskResource($task->fresh(['owner', 'category', 'worker']));
    }
}
