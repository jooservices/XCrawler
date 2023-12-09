<?php

namespace App\Modules\Client\Uri;

/**
 * Factory interface for uniform resource indicators.
 */
interface UriFactoryInterface
{
    /**
     * Factory method to build a URI from a super-global $_SERVER array.
     *
     * @return UriInterface
     */
    public function createFromSuperGlobalArray(array $server): UriInterface;

    /**
     * Creates a URI from an absolute URI.
     *
     * @param string $absoluteUri
     *
     * @return UriInterface
     */
    public function createFromAbsolute(string $absoluteUri): UriInterface;

    /**
     * Factory method to build a URI from parts.
     *
     * @param string $scheme
     * @param string $userInfo
     * @param string $host
     * @param int $port
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return UriInterface
     */
    public function createFromParts(
        string $scheme,
        string $userInfo,
        string $host,
        int $port,
        string $path = '',
        string $query = '',
        string $fragment = ''
    ): UriInterface;
}
