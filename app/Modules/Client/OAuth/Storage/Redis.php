<?php

namespace App\Modules\Client\OAuth\Storage;

use App\Modules\Client\OAuth\Exceptions\AuthorizationStateNotFoundException;
use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\Token\TokenInterface;
use Predis\Client;

class Redis implements TokenStorageInterface
{
    protected array $cachedTokens;

    protected array $cachedStates;

    /**
     * @param Client $redis An instantiated and connected redis client
     * @param string $key The key to store the token under in redis
     * @param string $stateKey the key to store the state under in redis
     */
    public function __construct(
        protected Client $redis,
        protected ?string $key = null,
        protected ?string $stateKey = null
    ) {
        $this->cachedTokens = [];
        $this->cachedStates = [];
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAccessToken(string $service): TokenInterface
    {
        if (!$this->hasAccessToken($service)) {
            throw new TokenNotFoundException('Token not found in redis');
        }

        if (isset($this->cachedTokens[$service])) {
            return $this->cachedTokens[$service];
        }

        $val = $this->redis->hget($this->key, $service);

        return $this->cachedTokens[$service] = unserialize($val);
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken(string $service, TokenInterface $token): self
    {
        // (over)write the token
        $this->redis->hset($this->key, $service, serialize($token));
        $this->cachedTokens[$service] = $token;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken(string $service): bool
    {
        if (
            isset($this->cachedTokens[$service])
            && $this->cachedTokens[$service] instanceof TokenInterface
        ) {
            return true;
        }

        return $this->redis->hexists($this->key, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken(string $service): self
    {
        $this->redis->hdel($this->key, $service);
        unset($this->cachedTokens[$service]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens(): self
    {
        // memory
        $this->cachedTokens = [];

        // redis
        $keys = $this->redis->hkeys($this->key);
        $me = $this; // 5.3 compat

        // pipeline for performance
        $this->redis->pipeline(
            function ($pipe) use ($keys, $me): void {
                foreach ($keys as $k) {
                    $pipe->hdel($me->getKey(), $k);
                }
            }
        );

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState(string $service): string
    {
        if (!$this->hasAuthorizationState($service)) {
            throw new AuthorizationStateNotFoundException('State not found in redis');
        }

        if (isset($this->cachedStates[$service])) {
            return $this->cachedStates[$service];
        }

        $val = $this->redis->hget($this->stateKey, $service);

        return $this->cachedStates[$service] = $val;
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState(string $service, string $state): self
    {
        // (over)write the token
        $this->redis->hset($this->stateKey, $service, $state);
        $this->cachedStates[$service] = $state;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState(string $service): bool
    {
        if (
            isset($this->cachedStates[$service])
            && null !== $this->cachedStates[$service]
        ) {
            return true;
        }

        return $this->redis->hexists($this->stateKey, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState(string $service): self
    {
        $this->redis->hdel($this->stateKey, $service);
        unset($this->cachedStates[$service]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates(): self
    {
        // memory
        $this->cachedStates = [];

        // redis
        $keys = $this->redis->hkeys($this->stateKey);
        $me = $this; // 5.3 compat

        // pipeline for performance
        $this->redis->pipeline(
            function ($pipe) use ($keys, $me): void {
                foreach ($keys as $k) {
                    $pipe->hdel($me->getKey(), $k);
                }
            }
        );

        // allow chaining
        return $this;
    }

    /**
     * @return Client $redis
     */
    public function getRedis(): Client
    {
        return $this->redis;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}
