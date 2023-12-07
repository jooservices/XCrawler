<?php

namespace App\Modules\Client\OAuth\Credentials;

use App\Modules\Client\Models\Integration;

class CredentialsFactory
{
    public function make(string $provider): CredentialsInterface
    {
        $integration = Integration::where('service', $provider)->first();

        if (!$integration) {
            throw new \Exception('Integration not found');
        }

        return new Credentials(
            $integration->key,
            $integration->secret,
            $integration->callback,
        );
    }
}
