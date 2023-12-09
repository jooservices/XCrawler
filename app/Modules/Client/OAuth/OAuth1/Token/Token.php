<?php

namespace App\Modules\Client\OAuth\OAuth1\Token;

use App\Modules\Client\OAuth\Token\BaseToken;
use App\Modules\Client\OAuth\OAuth1\Token\TokenInterface as OAuth1TokenInterface;

class Token extends BaseToken implements OAuth1TokenInterface
{
    protected string $requestToken;

    protected string $requestTokenSecret;

    protected string $accessTokenSecret;

    public function getRequestToken(): string
    {
        return $this->requestToken;
    }

    /**
     * @param string $requestToken
     */
    public function setRequestToken(string $requestToken): void
    {
        $this->requestToken = $requestToken;
    }

    public function getRequestTokenSecret(): string
    {
        return $this->requestTokenSecret;
    }

    public function setRequestTokenSecret(string $requestTokenSecret): void
    {
        $this->requestTokenSecret = $requestTokenSecret;
    }

    public function getAccessTokenSecret(): string
    {
        return $this->accessTokenSecret;
    }

    public function setAccessTokenSecret(string $accessTokenSecret): void
    {
        $this->accessTokenSecret = $accessTokenSecret;
    }
}
