<?php

namespace App\Modules\Client\StateMachine\Integration;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class IntegrationState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()->default(InitState::class)
            ->allowTransition(InitState::class, InProgressState::class)
            ->allowTransition(InProgressState::class, FailedState::class)
            ->allowTransition(InProgressState::class, CompletedState::class);
    }
}
