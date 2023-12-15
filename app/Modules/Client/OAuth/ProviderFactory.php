<?php

namespace App\Modules\Client\OAuth;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\ProviderInterface as OAuth1ProviderInterface;
use App\Modules\Client\OAuth\OAuth1\Token\Token;
use App\Modules\Core\Services\States;

class ProviderFactory
{
    public function oauth1(OAuth1ProviderInterface $provider, Integration $integration): OAuth1ProviderInterface
    {
        $provider->setCredentials($integration);

        /**
         * Integration already completed than use token from database
         * @TODO Consider combine both credentials and token into one object
         */
        if ($integration->state_code === States::STATE_COMPLETED) {
            $token = app(Token::class);
            $token->setAccessToken($integration->token);
            $token->setAccessTokenSecret($integration->token_secret);
            $provider->getStorage()->storeAccessToken(
                $integration->getUid(),
                $token
            );
        }

        $provider->init();

        return $provider;
    }
}
