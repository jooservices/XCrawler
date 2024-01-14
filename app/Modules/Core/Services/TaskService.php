<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Events\TaskCreatedEvent;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Repositories\TaskRepository;
use App\Modules\Core\StateMachine\Task\InProgressState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class TaskService
{
    public function create(TaskInterface $model, string $task): Task
    {
        /**
         * @var Task $task
         */
        $task = $model->tasks()->create(['task' => $task,]);

        Event::dispatch(new TaskCreatedEvent());

        return $task;
    }

    public function tasks(string $task, int $limit = 10): Collection
    {
        $tasks = app(TaskRepository::class)->tasks($task, $limit);

        Task::whereIn('id', $tasks->pluck('id'))
            ->update(['state_code' => InProgressState::class]);

        return $tasks;
    }
}
