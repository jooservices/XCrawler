<?php

namespace App\Modules\Client\Repositories;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Models\Integration;
use App\Modules\Core\Exceptions\HaveNoIntegration;
use App\Modules\Core\Services\States;
use Exception;
use Illuminate\Support\Collection;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class IntegrationRepository
{
    public function getCompleted(string $service): Collection
    {
        return Integration::where('service', $service)
            ->where('state_code', States::STATE_COMPLETED)->get();
    }

    public function getNotIntegrated(string $service): Collection
    {
        return Integration::where('service', $service)
            ->where('state_code', States::STATE_INIT)->get();
    }

    public function getPrimary(string $service, string $stateCode = States::STATE_COMPLETED): Integration
    {
        return Integration::where('service', $service)
            ->where('is_primary', true)
            ->where('state_code', $stateCode)
            ->first();
    }

    /**
     * @throws HaveNoIntegration
     */
    public function getNonPrimary(string $service, string $stateCode = States::STATE_COMPLETED): Integration
    {
        $integration = Integration::where('service', $service)
            ->where('is_primary', false)
            ->where('state_code', $stateCode)
            ->orderBy('requested_times', 'ASC')
            ->first();

        if (!$integration) {
            throw new HaveNoIntegration('There is no integration for ' . $service);
        }

        return $integration;
    }

    public function getNonPrimaryItems(string $service, string $stateCode = States::STATE_COMPLETED): Collection
    {
        $integrations = Integration::where('service', $service)
            ->where('is_primary', false)
            ->where('state_code', $stateCode)
            ->orderBy('requested_times', 'ASC')
            ->get();

        if ($integrations->isEmpty()) {
            throw new HaveNoIntegration('There are no integration for ' . $service);
        }

        return $integrations;
    }
}
