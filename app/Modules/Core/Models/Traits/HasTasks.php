<?php

namespace App\Modules\Core\Models\Traits;

use App\Modules\Core\Models\Task;
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
            'payload' => $payload,
        ]);
    }
}
