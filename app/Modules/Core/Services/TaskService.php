<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Task;
use Illuminate\Support\Collection;

class TaskService
{
    public function tasks(string $task, int $limit): Collection
    {
        return Task::where('task', $task)
            ->where('state_code', States::STATE_INIT)
            ->limit($limit)
            ->get();
    }
}
