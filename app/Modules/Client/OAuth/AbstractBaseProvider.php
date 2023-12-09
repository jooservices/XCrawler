<?php

namespace App\Modules\Client\OAuth;

use App\Modules\Client\OAuth\Credentials\CredentialsInterface;
use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use App\Modules\Client\Services\XClient;
use App\Modules\Client\Uri\Uri;
use App\Modules\Client\Uri\UriInterface;
use Exception;

abstract class AbstractBaseProvider implements ProviderInterface
{
    protected CredentialsInterface $credentials;

    /**
     * @TODO Remove Uri object
     */
    protected ?Uri $baseApiUri;

    public function __construct(
        protected XClient $client,
        protected TokenStorageInterface $storage,
    ) {
    }

    /**
     * Accessor to the storage adapter to be able to retrieve tokens.
     *
     * @return TokenStorageInterface
     */
    public function getStorage(): TokenStorageInterface
    {
        return $this->storage;
    }

    public function setCredentials(CredentialsInterface $credentials): void
    {
        $this->credentials = $credentials;
    }

    abstract public function init(string $baseApiUri = null);

    /**
     * @param string|UriInterface $path
     * @param UriInterface|null $baseApiUri
     *
     * @return UriInterface
     * @throws Exception
     */
    protected function determineRequestUriFromPath($path, ?UriInterface $baseApiUri = null): UriInterface
    {
        if ($path instanceof UriInterface) {
            return $path;
        }

        if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
            return new Uri($path);
        }

        if ($baseApiUri === null) {
            throw new Exception(
                'An absolute URI must be passed to ServiceInterface::request as no baseApiUri is set.'
            );
        }

        $uri = clone $baseApiUri;
        if (str_contains($path, '?')) {
            $parts = explode('?', $path, 2);
            $path = $parts[0];
            $query = $parts[1];
            $uri->setQuery($query);
        }

        if ($path[0] === '/') {
            $path = substr($path, 1);
        }

        $uri->setPath($uri->getPath() . $path);

        return $uri;
    }
}
