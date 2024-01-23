<?php

namespace App\Modules\Core\Console\Traits;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;

trait HasIntegrationsCommand
{
    /**
     * @throws NoIntegrateException
     */
    public function processCompletedIntegrations(string $service, callable $callback): void
    {
        app(IntegrationRepository::class)
            ->getCompleted($service)
            ->each(function ($integration) use ($callback) {
                $this->output->text('Processing integration: <options=bold;fg=blue>' . $integration->name .'</>');
                $callback($integration);
            });
    }

    public function processNonePrimaryIntegrations(string $service, callable $callback): void
    {
        app(IntegrationRepository::class)
            ->getNonPrimaryItems($service)
            ->each(function ($integration) use ($callback) {
                $this->output->text('Processing integration: <options=bold;fg=blue>' . $integration->name .'</>');
                $callback($integration);
            });
    }
}
