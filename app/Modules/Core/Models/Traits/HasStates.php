<?php

namespace App\Modules\Core\Models\Traits;

use ReflectionClass;

trait HasStates
{
    public function isState(string $stateClass): bool
    {
        return $this->state_code->getValue() === $stateClass;
    }

    public function transitionTo(string $stateClass): void
    {
        if ($this->state_code->getValue() === $stateClass) {
            return;
        }

        $this->state_code->transitionTo($stateClass);
    }

    public function isCompletedState(): bool
    {
        return (new ReflectionClass($this->state_code))->getConstant('STATE_CODE') === 'COMPLETED';
    }

    public function isFailedState(): bool
    {
        return (new ReflectionClass($this->state_code))->getConstant('STATE_CODE') === 'FAILED';
    }
}
