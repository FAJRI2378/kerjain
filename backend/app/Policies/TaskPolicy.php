<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'hirer';
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->owner_id && $task->status === 'pending';
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->role === 'admin'
            || ($user->id === $task->owner_id && ! in_array($task->status, ['in_progress', 'reviewing']));
    }

    public function approve(User $user, Task $task): bool
    {
        return $user->role === 'admin' && $task->status === 'pending';
    }

    public function reject(User $user, Task $task): bool
    {
        return $user->role === 'admin' && $task->status === 'pending';
    }

    public function apply(User $user, Task $task): bool
    {
        return $user->role === 'freelancer'
            && $user->id !== $task->owner_id
            && $task->status === 'approved';
    }

    public function submit(User $user, Task $task): bool
    {
        return $user->role === 'freelancer'
            && $task->worker_id === $user->id
            && $task->status === 'in_progress';
    }

    public function complete(User $user, Task $task): bool
    {
        return $user->id === $task->owner_id && $task->status === 'reviewing';
    }

    public function manageApplications(User $user, Task $task): bool
    {
        return $user->id === $task->owner_id;
    }
}
