<?php

namespace App\Modules\Client\OAuth\Storage;

use App\Modules\Client\OAuth\Exceptions\AuthorizationStateNotFoundException;
use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\Token\TokenInterface;
use App\Modules\Core\Storage\Storage;

class Memory extends Storage implements TokenStorageInterface
{
    private const STATES_KEY = '_states';

    /**
     * @param string $service
     * @return TokenInterface
     * @throws TokenNotFoundException
     */
    public function retrieveAccessToken(string $service): TokenInterface
    {
        if ($this->hasAccessToken($service)) {
            return $this->getProperty($service);
        }

        throw new TokenNotFoundException('Token not stored');
    }

    /**
     * @param string $service
     * @return bool
     */
    public function hasAccessToken(string $service): bool
    {
        return $this->hasProperty($service)
            && $this->getProperty($service) instanceof TokenInterface;
    }

    /**
     * @param string $service
     * @param TokenInterface $token
     * @return $this
     */
    public function storeAccessToken(string $service, TokenInterface $token): self
    {
        $this->setProperty($service, $token);

        return $this;
    }

    /**
     * @param string $service
     * @return $this
     */
    public function clearToken(string $service): self
    {
        $this->removeProperty($service);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearAllTokens(): self
    {
        $this->reset();

        return $this;
    }

    /**
     * @param string $service
     * @param string $state
     * @return $this
     */
    public function storeAuthorizationState(string $service, string $state): self
    {
        $states = $this->getProperty(self::STATES_KEY, []);
        $states[$service] = $state;

        $this->setProperty(self::STATES_KEY, $states);

        return $this;
    }

    /**
     * @param string $service
     * @return string
     * @throws AuthorizationStateNotFoundException
     */
    public function retrieveAuthorizationState(string $service): string
    {
        if (!$this->hasAuthorizationState($service)) {
            throw new AuthorizationStateNotFoundException('State not stored');
        }

        return $this->getProperty(self::STATES_KEY)[$service];
    }

    /**
     * @param string $service
     * @return bool
     */
    public function hasAuthorizationState(string $service): bool
    {
        $states = $this->getProperty(self::STATES_KEY, []);

        return isset($states[$service]);
    }

    /**
     * @param string $service
     * @return $this
     */
    public function clearAuthorizationState(string $service): self
    {
        $states = $this->getProperty(self::STATES_KEY, []);
        $states[$service] = null;
        $this->setProperty(self::STATES_KEY, $states);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearAllAuthorizationStates(): self
    {
        $this->setProperty(self::STATES_KEY, []);

        return $this;
    }
}
