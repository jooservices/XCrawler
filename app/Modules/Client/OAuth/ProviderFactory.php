<?php

namespace App\Modules\Client\OAuth;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Token\Token;

class ProviderFactory
{
    public function make(ProviderInterface $provider): ProviderInterface
    {
        $integration = Integration::where('service', $provider->service())->first();

        if ($integration) {
            $token = app(Token::class);
            $token->setAccessToken($integration->token);
            $token->setAccessTokenSecret($integration->token_secret);
            $provider->getStorage()->storeAccessToken(
                $provider->service(),
                $token
            );
        }

        return $provider;
    }
}
