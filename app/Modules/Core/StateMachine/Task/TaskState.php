<?php

namespace App\Modules\Core\StateMachine\Task;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class TaskState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()->default(InitState::class)
            ->allowTransition(InitState::class, InProgressState::class)
            # In Progress
            ->allowTransition(InProgressState::class, FailedState::class)
            ->allowTransition(InProgressState::class, CompletedState::class)
            ->allowTransition(InProgressState::class, RecurringState::class)
            ->allowTransition(InProgressState::class, DownloadedState::class)
            # Recurring
            ->allowTransition(RecurringState::class, CompletedState::class)
            ->allowTransition(RecurringState::class, FailedState::class);
    }
}
