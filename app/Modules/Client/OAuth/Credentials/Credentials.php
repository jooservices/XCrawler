<?php

namespace App\Modules\Client\OAuth\Credentials;

class Credentials implements CredentialsInterface
{
    public function __construct(
        private string $consumerId,
        private string $consumerSecret,
        private string $callbackUrl
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

    public function getUid(): string
    {
        return md5($this->consumerId . $this->consumerSecret . $this->callbackUrl);
    }
}
