<?php

namespace App\Modules\Core\Models\Traits;

use App\Modules\Core\Services\States;

trait HasStates
{
    public static function getStates(): array
    {
        return [
            States::STATE_IN_PROGRESS,
            States::STATE_COMPLETED,
            States::STATE_FAILED,
            States::STATE_RECURRING,
        ];
    }

    public function scopeByState($query, string $stateCode)
    {
        return $query->where('state_code', $stateCode);
    }

    public function updateState(string $stateCode): void
    {
        $this->update([
            'state_code' => $stateCode,
        ]);
    }
}
