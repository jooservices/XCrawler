<?php

namespace App\Modules\Client\OAuth\Storage;

use App\Modules\Client\OAuth\Exceptions\AuthorizationStateNotFoundException;
use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\Token\TokenInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class Session implements TokenStorageInterface
{
    /**
     * @param bool $startSession whether or not to start the session upon construction
     * @param string $sessionVariableName the variable name to use within the _SESSION superglobal
     * @param string $stateVariableName
     */
    public function __construct(
        private bool $startSession = true,
        private string $sessionVariableName = 'xcrawler-oauth-token',
        private string $stateVariableName = 'xcrawler-oauth-state'
    ) {
        if ($startSession && !$this->sessionHasStarted()) {
            session_start();
        }

        $this->startSession = $startSession;
        $this->sessionVariableName = $sessionVariableName;
        $this->stateVariableName = $stateVariableName;
        if (!isset($_SESSION[$sessionVariableName])) {
            $_SESSION[$sessionVariableName] = [];
        }
        if (!isset($_SESSION[$stateVariableName])) {
            $_SESSION[$stateVariableName] = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAccessToken(string $service): TokenInterface
    {
        if ($this->hasAccessToken($service)) {
            return unserialize($_SESSION[$this->sessionVariableName][$service]);
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken(string $service, TokenInterface $token): self
    {
        $serializedToken = serialize($token);

        if (
            isset($_SESSION[$this->sessionVariableName])
            && is_array($_SESSION[$this->sessionVariableName])
        ) {
            $_SESSION[$this->sessionVariableName][$service] = $serializedToken;
            return $this;
        }

        $_SESSION[$this->sessionVariableName] = [$service => $serializedToken,];

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken(string $service): bool
    {
        return isset($_SESSION[$this->sessionVariableName], $_SESSION[$this->sessionVariableName][$service]);
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken(string $service): self
    {
        if (array_key_exists($service, $_SESSION[$this->sessionVariableName])) {
            unset($_SESSION[$this->sessionVariableName][$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens(): self
    {
        unset($_SESSION[$this->sessionVariableName]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState(string $service, string $state): self
    {
        if (
            isset($_SESSION[$this->stateVariableName])
            && is_array($_SESSION[$this->stateVariableName])
        ) {
            $_SESSION[$this->stateVariableName][$service] = $state;
        } else {
            $_SESSION[$this->stateVariableName] = [
                $service => $state,
            ];
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState(string $service): bool
    {
        return isset($_SESSION[$this->stateVariableName], $_SESSION[$this->stateVariableName][$service]);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState(string $service): string
    {
        if ($this->hasAuthorizationState($service)) {
            return $_SESSION[$this->stateVariableName][$service];
        }

        throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState(string $service): self
    {
        if (array_key_exists($service, $_SESSION[$this->stateVariableName])) {
            unset($_SESSION[$this->stateVariableName][$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates(): self
    {
        unset($_SESSION[$this->stateVariableName]);

        // allow chaining
        return $this;
    }

    public function __destruct()
    {
        if ($this->startSession) {
            session_write_close();
        }
    }

    /**
     * Determine if the session has started.
     *
     * @url http://stackoverflow.com/a/18542272/1470961
     *
     * @return bool
     */
    protected function sessionHasStarted()
    {
        // For more modern PHP versions we use a more reliable method.
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return session_status() != PHP_SESSION_NONE;
        }

        // Below PHP 5.4 we should test for the current session ID.
        return session_id() !== '';
    }
}
