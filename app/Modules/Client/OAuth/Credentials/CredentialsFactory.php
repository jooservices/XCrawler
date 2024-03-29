<?php

namespace App\Modules\Client\OAuth\Credentials;

/**
 * Factory from env
 */
class CredentialsFactory
{
    /**
     * @param string $provider
     * @return CredentialsInterface
     */
    public function make(string $provider): CredentialsInterface
    {
        return new Credentials(
            config('client.' . $provider . '.key'),
            config('client.' . $provider . '.secret'),
            config('client.' . $provider . '.callback'),
        );
    }
}
