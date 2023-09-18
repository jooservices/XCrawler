<?php

namespace App\Modules\Client\OAuth\Token;

abstract class AbstractBaseToken implements TokenInterface
{
    protected int $endOfLife;

    public function __construct(
        protected ?string $accessToken = null,
        protected ?string $refreshToken = null,
        protected ?int $lifetime = null,
        protected array $extraParams = []
    ) {
        $this->setLifetime($lifetime);
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getEndOfLife(): int
    {
        return $this->endOfLife;
    }

    public function setExtraParams(array $extraParams): void
    {
        $this->extraParams = $extraParams;
    }

    /**
     * @return array
     */
    public function getExtraParams(): array
    {
        return $this->extraParams;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param int $endOfLife
     */
    public function setEndOfLife($endOfLife): void
    {
        $this->endOfLife = $endOfLife;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime($lifetime): void
    {
        if ($lifetime === 0 || static::EOL_NEVER_EXPIRES === $lifetime) {
            $this->endOfLife = static::EOL_NEVER_EXPIRES;
            return;
        } elseif ($lifetime !== null) {
            $this->endOfLife = (int)$lifetime + time();
            return;
        }

        $this->endOfLife = static::EOL_UNKNOWN;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function isExpired(): bool
    {
        return $this->getEndOfLife() !== TokenInterface::EOL_NEVER_EXPIRES
            && $this->getEndOfLife() !== TokenInterface::EOL_UNKNOWN
            && time() > $this->getEndOfLife();
    }

    public function __sleep()
    {
        return ['accessToken'];
    }
}
