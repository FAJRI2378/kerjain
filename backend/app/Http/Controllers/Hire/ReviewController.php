<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hire\ReviewRequest;
use App\Models\Rating;
use App\Models\Task;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function completed(Request $request)
    {
        $tasks = Task::where('owner_id', $request->user()->id)
            ->where('status', 'completed')
            ->with(['worker', 'ratings'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'data' => [
                'tasks' => $tasks->map(function (Task $task) use ($request) {
                    $myRating = $task->ratings->firstWhere('reviewer_id', $request->user()->id);

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'worker_name' => $task->worker?->name,
                        'worker_id' => $task->worker_id,
                        'budget' => $task->budget,
                        'completed_date' => $task->updated_at?->translatedFormat('d M Y'),
                        'has_reviewed' => $myRating !== null,
                        'rating' => $myRating?->rating,
                        'comment' => $myRating?->comment,
                    ];
                }),
            ],
        ]);
    }

    public function review(ReviewRequest $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($task->status !== 'completed') {
            return response()->json(['message' => 'Only completed tasks can be reviewed.'], 409);
        }

        if (! $task->worker_id || $request->worker_id !== $task->worker_id) {
            return response()->json(['message' => 'The worker_id does not match the assigned worker.'], 422);
        }

        Rating::updateOrCreate(
            [
                'task_id' => $task->id,
                'reviewer_id' => $request->user()->id,
            ],
            [
                'reviewee_id' => $task->worker_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json(['message' => 'Ulasan & rating berhasil disimpan.']);
    }
}