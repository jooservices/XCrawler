<?php

namespace App\Modules\Core\Events;

use App\Modules\Core\Models\Task;

class TaskFailedEvent
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Task $task)
    {
    }
}
