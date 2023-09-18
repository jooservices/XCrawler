<?php

namespace App\Modules\Client\OAuth\OAuth1\Signature;

use App\Modules\Client\OAuth\Credentials\CredentialsInterface;
use App\Modules\Client\OAuth\Exceptions\UnsupportedHashAlgorithmException;
use App\Modules\Client\OAuth\Uri\UriInterface;

class Signature implements SignatureInterface
{
    protected string $algorithm = 'HMAC-SHA1';

    protected string $tokenSecret = '';

    public function __construct(protected CredentialsInterface $credentials)
    {
    }

    public function setHashingAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function setTokenSecret(string $token): self
    {
        $this->tokenSecret = $token;

        return $this;
    }

    /**
     * @param UriInterface $uri
     * @param array $params
     * @param string $method
     * @return string
     */
    public function getSignature(UriInterface $uri, array $params, string $method = 'POST'): string
    {
        parse_str($uri->getQuery(), $queryStringData);

        foreach ([...$queryStringData, ...$params] as $key => $value) {
            $signatureData[rawurlencode($key)] = rawurlencode($value);
        }

        ksort($signatureData);

        // determine base uri
        $baseUri = $uri->getScheme() . '://' . $uri->getRawAuthority();

        if ($uri->getPath() === '/') {
            $baseUri .= $uri->hasExplicitTrailingHostSlash() ? '/' : '';
        } else {
            $baseUri .= $uri->getPath();
        }

        $baseString = strtoupper($method) . '&';
        $baseString .= rawurlencode($baseUri) . '&';
        $baseString .= rawurlencode($this->buildSignatureDataString($signatureData));

        return base64_encode($this->hash($baseString));
    }

    /**
     * @return string
     */
    protected function buildSignatureDataString(array $signatureData): string
    {
        $signatureString = '';
        $delimiter = '';
        foreach ($signatureData as $key => $value) {
            $signatureString .= $delimiter . $key . '=' . $value;

            $delimiter = '&';
        }

        return $signatureString;
    }

    /**
     * @param $data
     * @return string
     * @throws UnsupportedHashAlgorithmException
     */
    protected function hash($data): string
    {
        switch (strtoupper($this->algorithm)) {
            case 'HMAC-SHA1':
                return hash_hmac('sha1', $data, $this->getSigningKey(), true);
            default:
                throw new UnsupportedHashAlgorithmException(
                    'Unsupported hashing algorithm (' . $this->algorithm . ') used.'
                );
        }
    }

    /**
     * @return string
     */
    protected function getSigningKey(): string
    {
        $signingKey = rawurlencode($this->credentials->getConsumerSecret()) . '&';
        if ($this->tokenSecret !== null) {
            $signingKey .= rawurlencode($this->tokenSecret);
        }

        return $signingKey;
    }
}
