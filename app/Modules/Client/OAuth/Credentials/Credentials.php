<?php

namespace App\Modules\Client\OAuth\Credentials;

class Credentials implements CredentialsInterface
{
    public function __construct(
        protected string $consumerId,
        protected string $consumerSecret,
        protected string $callbackUrl
    ) {
    }

    /**
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * @return string
     */
    public function getConsumerId(): string
    {
        return $this->consumerId;
    }

    /**
     * @return string
     */
    public function getConsumerSecret(): string
    {
        return $this->consumerSecret;
    }
}
