<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use Illuminate\Support\Collection;

class TaskRepository
{
    public function tasks(string $task, int $limit): Collection
    {
        return Task::where('task', $task)
            ->where('state_code', States::STATE_INIT)
            ->limit($limit)
            ->get();
    }
}
