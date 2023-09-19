<?php

namespace App\Modules\Client\OAuth\Storage;

use App\Modules\Client\OAuth\Exceptions\AuthorizationStateNotFoundException;
use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\Token\TokenInterface;

class Memory implements TokenStorageInterface
{
    public function __construct(protected array $tokens = [], protected array $states = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAccessToken(string $service): TokenInterface
    {
        if ($this->hasAccessToken($service)) {
            return $this->tokens[$service];
        }

        throw new TokenNotFoundException('Token not stored');
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken(string $service): bool
    {
        return isset($this->tokens[$service]) && $this->tokens[$service] instanceof TokenInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken(string $service, TokenInterface $token): self
    {
        $this->tokens[$service] = $token;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken(string $service): self
    {
        if (array_key_exists($service, $this->tokens)) {
            unset($this->tokens[$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens(): self
    {
        $this->tokens = [];

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState(string $service): string
    {
        if ($this->hasAuthorizationState($service)) {
            return $this->states[$service];
        }

        throw new AuthorizationStateNotFoundException('State not stored');
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState(string $service): bool
    {
        return isset($this->states[$service]) && null !== $this->states[$service];
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState(string $service, string $state): self
    {
        $this->states[$service] = $state;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState(string $service): self
    {
        if (array_key_exists($service, $this->states)) {
            unset($this->states[$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates(): self
    {
        $this->states = [];

        // allow chaining
        return $this;
    }
}
