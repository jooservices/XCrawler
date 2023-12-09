<?php

namespace App\Modules\Client\Uri;

interface UriInterface
{
    /**
     * @return string
     */
    public function getScheme();

    /**
     * @param string $scheme
     */
    public function setScheme(string $scheme);

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @param string $host
     */
    public function setHost(string $host);

    /**
     * @return int
     */
    public function getPort(): int;

    /**
     * @param int $port
     */
    public function setPort(int $port);

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     */
    public function setPath(string $path);

    /**
     * @return string
     */
    public function getQuery(): string;

    /**
     * @param string $query
     */
    public function setQuery(string $query);

    /**
     * Adds a param to the query string.
     *
     * @param string $var
     * @param string $val
     */
    public function addToQuery(string $var, string $val);

    /**
     * @return string
     */
    public function getFragment();

    /**
     * Should return URI user info, masking protected user info data according to rfc3986-3.2.1.
     *
     * @return string
     */
    public function getUserInfo(): string;

    /**
     * @param string $userInfo
     */
    public function setUserInfo(string $userInfo);

    /**
     * Should return the URI Authority, masking protected user info data according to rfc3986-3.2.1.
     *
     * @return string
     */
    public function getAuthority(): string;

    /**
     * Should return the URI string, masking protected user info data according to rfc3986-3.2.1.
     *
     * @return string the URI string with user protected info masked
     */
    public function __toString(): string;

    /**
     * Should return the URI Authority without masking protected user info data.
     *
     * @return string
     */
    public function getRawAuthority(): string;

    /**
     * Should return the URI user info without masking protected user info data.
     *
     * @return string
     */
    public function getRawUserInfo(): string;

    /**
     * Build the full URI based on all the properties.
     *
     * @return string The full URI without masking user info
     */
    public function getAbsoluteUri(): string;

    /**
     * Build the relative URI based on all the properties.
     *
     * @return string The relative URI
     */
    public function getRelativeUri(): string;

    /**
     * @return bool
     */
    public function hasExplicitTrailingHostSlash(): bool;

    /**
     * @return bool
     */
    public function hasExplicitPortSpecified(): bool;
}
