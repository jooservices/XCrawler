<?php

namespace App\Modules\Client\OAuth\OAuth1\Signature;

use App\Modules\Client\Uri\UriInterface;

interface SignatureInterface
{
    public function setHashingAlgorithm(string $algorithm): self;


    public function setTokenSecret(string $token): self;

    /**
     * @param UriInterface $uri
     * @param array $params
     * @param string $method
     * @return string
     */
    public function getSignature(UriInterface $uri, array $params, string $method = 'POST'): string;
}
