<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()->with(['owner', 'category', 'worker']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            if (in_array($status, ['pending', 'approved', 'rejected', 'in_progress', 'reviewing', 'completed', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        $tasks = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));

        return TaskResource::collection($tasks);
    }

    public function approve(Task $task)
    {
        if ($task->status !== 'pending') {
            return response()->json(['message' => 'Only pending tasks can be approved.'], 409);
        }

        $task->update(['status' => 'approved']);

        return response()->json(['message' => 'Task approved successfully.']);
    }

    public function reject(Task $task)
    {
        if ($task->status !== 'pending') {
            return response()->json(['message' => 'Only pending tasks can be rejected.'], 409);
        }

        $task->update(['status' => 'rejected']);

        return response()->json(['message' => 'Task rejected successfully.']);
    }

    public function destroy(Task $task)
    {
        if (in_array($task->status, ['in_progress', 'reviewing'])) {
            return response()->json(['message' => 'Cannot delete a task that is in progress.'], 409);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 204);
    }
}
