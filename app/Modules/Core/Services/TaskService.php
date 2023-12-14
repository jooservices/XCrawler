<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Events\TaskCreatedEvent;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Repositories\TaskRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class TaskService
{
    public function create(TaskInterface $model, string $task): Task
    {
        $task = $model->tasks()->create([
            'task' => $task,
            'state_code' => Task::STATE_INIT,
        ]);

        Event::dispatch(new TaskCreatedEvent($task));

        return $task;
    }

    public function tasks(string $task, int $limit): Collection
    {
        $tasks = app(TaskRepository::class)->tasks($task, $limit);

        Task::whereIn('id', $tasks->pluck('id'))->update([
            'state_code' => States::STATE_IN_PROGRESS,
        ]);

        return $tasks;
    }
}
