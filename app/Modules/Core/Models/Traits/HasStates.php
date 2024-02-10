<?php

namespace App\Modules\Core\Models\Traits;

use ReflectionClass;
use Spatie\ModelStates\State;

/**
 * @property State $state_code
 */
trait HasStates
{
    public function isState(string $stateClass): bool
    {
        return $this->state_code->getValue() === $stateClass;
    }

    public function transitionTo(string $stateClass): void
    {
        $this->refresh();
        if ($this->state_code->getValue() === $stateClass) {
            return;
        }

        /**
         * @phpstan-ignore-next-line
         */
        $this->state_code = $this->state_code->transitionTo($stateClass)->state_code;
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
