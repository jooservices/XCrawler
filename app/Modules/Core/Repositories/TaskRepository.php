<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Core\StateMachine\Task\InitState;
use Illuminate\Support\Collection;

class TaskRepository
{
    public function tasks(string $task, int $limit): Collection
    {
        return Task::where('task', $task)
            ->where('state_code', InitState::class)
            ->limit($limit)
            ->get();
    }
}
