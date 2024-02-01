<?php

namespace App\Modules\Core\Events;

use App\Modules\Core\Models\Task;
use Illuminate\Queue\SerializesModels;

class RecurredTaskEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Task $task)
    {
    }
}
