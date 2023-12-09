<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Task;
use Illuminate\Support\Collection;

class TaskService
{
    public function tasks(string $task, int $limit): Collection
    {
        $tasks = Task::where('task', $task)
            ->where('state_code', States::STATE_INIT)
            ->limit($limit)
            ->get();

        Task::whereIn('id', $tasks->pluck('id'))->update([
            'state_code' => States::STATE_IN_PROGRESS,
        ]);

        return $tasks;
    }
}
