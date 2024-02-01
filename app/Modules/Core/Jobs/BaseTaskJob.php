<?php

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Events\TaskCompletedEvent;
use App\Modules\Core\Events\TaskFailedEvent;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use Illuminate\Support\Facades\Event;
use Throwable;

abstract class BaseTaskJob extends BaseJob
{
    public Task $task;

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
        $this->task->transitionTo(FailedState::class);
        Event::dispatch(new TaskFailedEvent($this->task));

        $this->failedProcess($throwable);
    }

    public function completed(): void
    {
        $this->task->transitionTo(CompletedState::class);

        Event::dispatch(new TaskCompletedEvent($this->task));
    }

    abstract protected function failedProcess(Throwable $throwable): void;

    abstract protected function process(): bool;
}
