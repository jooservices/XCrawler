<?php

namespace App\Modules\Client\OAuth\OAuth1;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\ProviderInterface;
use App\Modules\Client\OAuth\OAuth1\Token\Token;
use App\Modules\Core\Services\States;

class IntegrationService
{
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly Integration $integration
    ) {
    }

    public function getAuthorizationUri(): string
    {
        return $this->provider->getAuthorizationUri(
            [
                'oauth_token' => $this->provider->requestRequestToken()->getRequestToken(),
                'perms' => 'read'
            ]
        )->getAbsoluteUri();
    }

    public function retrieveAccessToken(string $code): Token
    {
        $accessToken = $this->provider->retrieveAccessToken($code);
        $this->integration->update([
            'token_secret' => $accessToken->getAccessTokenSecret(),
            'token' => $accessToken->getAccessToken(),
            'state_code' => States::STATE_COMPLETED
        ]);

        return $accessToken;
    }
}
