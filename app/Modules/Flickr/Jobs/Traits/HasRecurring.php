<?php

namespace App\Modules\Flickr\Jobs\Traits;

use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Core\StateMachine\Task\RecurringState;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use Illuminate\Support\Facades\Event;

trait HasRecurring
{
    protected function recurringTask()
    {
        if ($this->task->isState(RecurringState::class)) {
            Event::dispatch(new RecurredTaskEvent($this->task));
            return;
        } elseif ($this->task->isState(InitState::class)) {
            $this->task->transitionTo(InProgressState::class);
        }

        $this->task->transitionTo(RecurringState::class);
        Event::dispatch(new RecurredTaskEvent($this->task));
    }
}
