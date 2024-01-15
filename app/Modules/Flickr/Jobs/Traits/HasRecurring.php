<?php

namespace App\Modules\Flickr\Jobs\Traits;

use App\Modules\Core\StateMachine\Task\RecurringState;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use Illuminate\Support\Facades\Event;

trait HasRecurring
{
    protected function recurringTask()
    {
        $this->task->transitionTo(RecurringState::class);

        Event::dispatch(new RecurredTaskEvent($this->task));
    }
}
