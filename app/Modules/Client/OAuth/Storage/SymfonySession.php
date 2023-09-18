<?php

namespace App\Modules\Client\OAuth\Storage;

use App\Modules\Client\OAuth\Exceptions\AuthorizationStateNotFoundException;
use App\Modules\Client\OAuth\Exceptions\TokenNotFoundException;
use App\Modules\Client\OAuth\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SymfonySession implements TokenStorageInterface
{
    /**
     * @param bool $startSession
     * @param string $sessionVariableName
     * @param string $stateVariableName
     */
    public function __construct(
        private Session $session,
        private bool $startSession = true,
        private string $sessionVariableName = 'xcrawler_oauth_token',
        private string $stateVariableName = 'xcrawler_oauth_state'
    ) {
        if (!$this->session->isStarted() && !in_array(session_status(), [PHP_SESSION_NONE, PHP_SESSION_ACTIVE])) {
            $this->session->start();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAccessToken(string $service): TokenInterface
    {
        if ($this->hasAccessToken($service)) {
            // get from session
            $tokens = $this->session->get($this->sessionVariableName);

            // one item
            return $tokens[$service];
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken(string $service, TokenInterface $token): self
    {
        // get previously saved tokens
        $tokens = $this->session->get($this->sessionVariableName);

        if (!is_array($tokens)) {
            $tokens = [];
        }

        $tokens[$service] = $token;

        // save
        $this->session->set($this->sessionVariableName, $tokens);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken(string $service): bool
    {
        // get from session
        $tokens = $this->session->get($this->sessionVariableName);

        return is_array($tokens)
            && isset($tokens[$service])
            && $tokens[$service] instanceof TokenInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken($service): self
    {
        // get previously saved tokens
        $tokens = $this->session->get($this->sessionVariableName);

        if (is_array($tokens) && array_key_exists($service, $tokens)) {
            unset($tokens[$service]);

            // Replace the stored tokens array
            $this->session->set($this->sessionVariableName, $tokens);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens(): self
    {
        $this->session->remove($this->sessionVariableName);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState(string $service): string
    {
        if ($this->hasAuthorizationState($service)) {
            // get from session
            $states = $this->session->get($this->stateVariableName);

            // one item
            return $states[$service];
        }

        throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState(string $service, string $state): self
    {
        // get previously saved tokens
        $states = $this->session->get($this->stateVariableName);

        if (!is_array($states)) {
            $states = [];
        }

        $states[$service] = $state;

        // save
        $this->session->set($this->stateVariableName, $states);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState(string $service): bool
    {
        // get from session
        $states = $this->session->get($this->stateVariableName);

        return is_array($states)
            && isset($states[$service])
            && null !== $states[$service];
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState(string $service): self
    {
        // get previously saved tokens
        $states = $this->session->get($this->stateVariableName);

        if (is_array($states) && array_key_exists($service, $states)) {
            unset($states[$service]);

            // Replace the stored tokens array
            $this->session->set($this->stateVariableName, $states);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates(): self
    {
        $this->session->remove($this->stateVariableName);

        // allow chaining
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }
}
