<?php

namespace App\Modules\Flickr\Jobs\Traits;

use App\Modules\Core\Events\RecurredTaskEvent;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Core\StateMachine\Task\RecurringState;
use Illuminate\Support\Facades\Event;

trait HasRecurringTask
{
    protected function recurringTask(...$args): bool
    {
        if ($this->task->isState(InitState::class)) {
            $this->task->transitionTo(InProgressState::class);
        }

        if ($this->task->isState(InProgressState::class)) {
            $this->task->transitionTo(RecurringState::class);
        }

        Event::dispatch(new RecurredTaskEvent($this->task));

        call_user_func_array([__CLASS__, 'dispatch'], $args)
            ->onQueue($this->queue);

        return false;
    }
}
