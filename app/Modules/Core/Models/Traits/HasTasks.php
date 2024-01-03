<?php

namespace App\Modules\Core\Models\Traits;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTasks
{
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'model');
    }

    public function createTask(string $task, ?array $payload = null)
    {
        return $this->tasks()->create([
            'task' => $task,
            'status' => States::STATE_INIT,
            'payload' => $payload,
        ]);
    }
}
