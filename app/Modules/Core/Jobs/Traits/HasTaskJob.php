<?php

namespace App\Modules\Core\Jobs\Traits;

use App\Modules\Core\StateMachine\Task\FailedState;
use Throwable;

trait HasTaskJob
{
    public function failed(Throwable $throwable)
    {
        if (isset($this->task)) {
            $this->task->transitionTo(FailedState::class);
        }

        $this->failedProcess($throwable);
    }

    abstract protected function failedProcess(Throwable $throwable): void;
}
