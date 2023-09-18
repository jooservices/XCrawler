<?php

namespace App\Modules\Client\OAuth;

use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use App\Modules\Client\OAuth\Uri\UriInterface;
use App\Modules\Client\Responses\XResponseInterface;

interface ProviderInterface
{
    /**
     * Sends an authenticated API request to the path provided.
     * If the path provided is not an absolute URI, the base API Uri (service-specific) will be used.
     *
     * @param string|UriInterface $path
     * @param string $method HTTP method
     * @param array $body Request body if applicable (an associative array will
     *                              automatically be converted into a urlencoded body)
     * @param array $extraHeaders Extra headers if applicable. These will override service-specific
     *                              any defaults.
     *
     * @return XClientResponseInterface
     */
    public function request(
        $path,
        array $body = [],
        array $extraHeaders = [],
        string $method = 'GET'
    ): XResponseInterface;

    /**
     * Returns the url to redirect to for authorization purposes.
     *
     * @return UriInterface
     */
    public function getAuthorizationUri(array $additionalParameters = []): UriInterface;

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint(): UriInterface;

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint(): UriInterface;

    public function getStorage(): TokenStorageInterface;

    public function service(): string;
}
