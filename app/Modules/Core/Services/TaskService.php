<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\HasTasksInterface;

class TaskService
{
    public function add(HasTasksInterface $model, string $task)
    {
        $model->tasks()->create([
            $task,
            'state_code' => States::STATE_IN_PROGRESS,
        ]);
    }
}
