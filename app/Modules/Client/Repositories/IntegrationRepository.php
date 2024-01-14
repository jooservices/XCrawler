<?php

namespace App\Modules\Client\Repositories;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\StateMachine\Integration\InitState;
use Illuminate\Support\Collection;

class IntegrationRepository
{
    /**
     * @throws NoIntegrateException
     */
    public function getItems(
        string $service,
        ?bool $isPrimary = null,
        string $stateCode = InitState::class
    ): Collection {
        $integrations = Integration::where('service', $service)
            ->when($isPrimary !== null, function ($query) use ($isPrimary) {
                $query->where('is_primary', $isPrimary);
            })
            ->whereState('state_code', $stateCode)
            ->get();

        if ($integrations->isEmpty()) {
            throw new NoIntegrateException('There are no integration for ' . $service);
        }

        return $integrations;
    }

    public function getInit(string $service, ?bool $isPrimary = null): Collection
    {
        return $this->getItems($service, $isPrimary);
    }

    /**
     * @throws NoIntegrateException
     */
    public function getCompleted(string $service, ?bool $isPrimary = null): Collection
    {
        return $this->getItems($service, $isPrimary, CompletedState::class);
    }

    /**
     * @throws NoIntegrateException
     */
    public function getPrimary(string $service, string $stateCode = CompletedState::class): Integration
    {
        return $this->getItems($service, true, $stateCode)->first();
    }

    /**
     * @throws NoIntegrateException
     */
    public function getNonPrimary(string $service, string $stateCode = CompletedState::class): Integration
    {
        return $this->getItems($service, false, $stateCode)->first();
    }

    /**
     * @throws NoIntegrateException
     */
    public function getNonPrimaryItems(string $service, string $stateCode = CompletedState::class): Collection
    {
        return $this->getItems($service, false, $stateCode);
    }
}
