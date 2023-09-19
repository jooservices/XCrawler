<?php

namespace App\Modules\Client\OAuth\OAuth1\Token;

use App\Modules\Client\OAuth\Token\TokenInterface as BaseTokenInterface;

interface TokenInterface extends BaseTokenInterface
{
    /**
     * @return string
     */
    public function getAccessTokenSecret();

    /**
     * @param string $accessTokenSecret
     */
    public function setAccessTokenSecret(string $accessTokenSecret);

    /**
     * @return string
     */
    public function getRequestTokenSecret();

    /**
     * @param string $requestTokenSecret
     */
    public function setRequestTokenSecret(string $requestTokenSecret);

    /**
     * @return string
     */
    public function getRequestToken();

    /**
     * @param string $requestToken
     */
    public function setRequestToken(string $requestToken);
}
