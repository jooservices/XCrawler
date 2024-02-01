<?php

namespace App\Modules\Core\Jobs;

use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use Throwable;

abstract class BaseTaskJob extends BaseJob
{
    public function handle(): void
    {
        $this->prepareState();

        if ($this->process()) {
            $this->completed();
        }
    }

    public function prepareState(): void
    {
        if ($this->task->isState(InitState::class)) {
            $this->task->transitionTo(InProgressState::class);
        }
    }

    public function failed(Throwable $throwable): void
    {
        if (isset($this->task)) {
            $this->task->transitionTo(FailedState::class);
        }

        $this->failedProcess($throwable);
    }

    public function completed(): void
    {
        $this->task->transitionTo(CompletedState::class);
    }

    abstract protected function failedProcess(Throwable $throwable): void;

    abstract protected function process(): bool;
}
