<?php

namespace App\Modules\Client\OAuth;

use App\Modules\Client\OAuth\Credentials\CredentialsFactory;
use App\Modules\Client\OAuth\Credentials\CredentialsInterface;
use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use App\Modules\Client\OAuth\Uri\Uri;
use App\Modules\Client\OAuth\Uri\UriInterface;
use App\Modules\Client\Services\XClient;
use Exception;

abstract class AbstractBaseProvider implements ProviderInterface
{
    protected CredentialsInterface $credentials;

    public function __construct(protected TokenStorageInterface $storage, protected XClient $client)
    {
        $this->credentials = app(CredentialsFactory::class)->make($this->service());
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
