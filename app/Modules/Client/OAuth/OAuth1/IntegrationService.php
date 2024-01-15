<?php

namespace App\Modules\Client\OAuth\OAuth1;

use App\Modules\Client\Exceptions\CannotGetAccessTokenException;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\ProviderInterface;
use App\Modules\Client\OAuth\OAuth1\Token\Token;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\StateMachine\Integration\FailedState;
use Exception;

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

    /**
     * @throws CannotGetAccessTokenException
     */
    public function retrieveAccessToken(string $code): Token
    {
        try {
            $accessToken = $this->provider->retrieveAccessToken($code);
            $this->integration->transitionTo(CompletedState::class);
            $this->integration->update([
                'token_secret' => $accessToken->getAccessTokenSecret(),
                'token' => $accessToken->getAccessToken(),
            ]);
        } catch (Exception $e) {
            $this->integration->transitionTo(FailedState::class);
            throw new CannotGetAccessTokenException($e->getMessage());
        }

        return $accessToken;
    }
}
