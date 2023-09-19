<?php

namespace App\Modules\Client\OAuth\Credentials;

class CredentialsFactory
{
    public function make(string $provider): CredentialsInterface
    {
        return new Credentials(
            config('client.' . $provider . '.key'),
            config('client.' . $provider . '.secret'),
            config('client.' . $provider . '.callback'),
        );
    }
}
