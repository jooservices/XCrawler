<?php

namespace App\Modules\Core\Console\Traits;

use App\Modules\Core\Services\TaskService;

trait HasTasksCommand
{
    protected function processTasks(string $task, int $limit, callable $callback)
    {
        app(TaskService::class)->tasks($task, $limit)->each(function ($task) use ($callback) {
            $this->output->text('Processing task: ' . $task->model_type . ' ' . $task->model_id);
            $callback($task);
        });
    }
}
