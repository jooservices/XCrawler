<?php

namespace App\Modules\Flickr\Console\Traits;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;

trait HasIntegrationProcess
{
    /**
     * @throws NoIntegrateException
     */
    public function completed(string $service, callable $callback): void
    {
        app(IntegrationRepository::class)
            ->getCompleted($service)
            ->each(function ($integration) use ($callback) {
                $this->output->text('Processing integration: ' . $integration->name);
                $callback($integration);
            });
    }
}
