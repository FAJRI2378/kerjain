<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskApplicationResource;
use App\Models\Task;
use App\Models\TaskApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index(Request $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        $applications = $task->applications()
            ->with('worker')
            ->orderByDesc('created_at')
            ->get();

        return TaskApplicationResource::collection($applications);
    }

    public function accept(Request $request, Task $task, TaskApplication $application)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($application->task_id !== $task->id) {
            return response()->json(['message' => 'Application does not belong to this task.'], 404);
        }

        try {
            $result = DB::transaction(function () use ($task, $application) {
                if ($task->status !== 'approved') {
                    return ['error' => 'This task is no longer accepting applicants.', 'code' => 409];
                }

                if ($application->status !== 'pending') {
                    return ['error' => 'This application is already processed.', 'code' => 409];
                }

                $application->update(['status' => 'accepted']);

                $task->update([
                    'worker_id' => $application->worker_id,
                    'status' => 'in_progress',
                ]);

                $task->applications()
                    ->where('id', '!=', $application->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);

                return null;
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to accept application.'], 500);
        }

        if ($result) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json(['message' => 'Worker assigned successfully.']);
    }

    public function reject(Request $request, Task $task, TaskApplication $application)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($application->task_id !== $task->id) {
            return response()->json(['message' => 'Application does not belong to this task.'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'This application is already processed.'], 409);
        }

        $application->update(['status' => 'rejected']);

        return response()->json(['message' => 'Application rejected.']);
    }
}
