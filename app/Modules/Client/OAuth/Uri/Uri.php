<?php

namespace App\Modules\Client\OAuth\Uri;

use InvalidArgumentException;

/**
 * Standards-compliant URI class.
 */
class Uri implements UriInterface
{
    /**
     * @var string
     */
    private string $scheme = 'http';

    /**
     * @var string
     */
    private string $userInfo = '';

    /**
     * @var string
     */
    private string $rawUserInfo = '';

    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port = 80;

    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var string
     */
    private string $query = '';

    /**
     * @var string
     */
    private string $fragment = '';

    /**
     * @var bool
     */
    private bool $explicitPortSpecified = false;

    /**
     * @var bool
     */
    private bool $explicitTrailingHostSlash = false;

    /**
     * @param string|null $uri
     */
    public function __construct(string $uri = null)
    {
        if (null !== $uri) {
            $this->parseUri($uri);
        }
    }

    /**
     * @param string $uri
     */
    protected function parseUri(string $uri): void
    {
        if (false === ($uriParts = parse_url($uri))) {
            // congratulations if you've managed to get parse_url to fail,
            // it seems to always return some semblance of a parsed url no matter what
            throw new InvalidArgumentException("Invalid URI: $uri");
        }

        if (!isset($uriParts['scheme'])) {
            throw new InvalidArgumentException('Invalid URI: http|https scheme required');
        }

        $this->scheme = $uriParts['scheme'];
        $this->host = $uriParts['host'];

        if (isset($uriParts['port'])) {
            $this->port = $uriParts['port'];
            $this->explicitPortSpecified = true;
        } else {
            $this->port = strcmp('https', $uriParts['scheme']) ? 80 : 443;
            $this->explicitPortSpecified = false;
        }

        if (isset($uriParts['path'])) {
            $this->path = $uriParts['path'];
            if ('/' === $uriParts['path']) {
                $this->explicitTrailingHostSlash = true;
            }
        } else {
            $this->path = '/';
        }

        $this->query = $uriParts['query'] ?? '';
        $this->fragment = $uriParts['fragment'] ?? '';

        $userInfo = '';
        if (!empty($uriParts['user'])) {
            $userInfo .= $uriParts['user'];
        }
        if ($userInfo && !empty($uriParts['pass'])) {
            $userInfo .= ':' . $uriParts['pass'];
        }

        $this->setUserInfo($userInfo);
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * @param string $userInfo
     */
    public function setUserInfo(string $userInfo): void
    {
        $this->userInfo = $userInfo ? $this->protectUserInfo($userInfo) : '';
        $this->rawUserInfo = $userInfo;
    }

    /**
     * @return string
     */
    public function getRawUserInfo(): string
    {
        return $this->rawUserInfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;

        if (
            ('https' === $this->scheme && $this->port === 443)
            || ('http' === $this->scheme && $this->port === 80)
        ) {
            $this->explicitPortSpecified = false;
            return;
        }

        $this->explicitPortSpecified = true;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function setPath($path): void
    {
        if (empty($path)) {
            $this->path = '/';
            $this->explicitTrailingHostSlash = false;
        } else {
            $this->path = $path;
            if ('/' === $this->path) {
                $this->explicitTrailingHostSlash = true;
            }
        }
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment(string $fragment): void
    {
        $this->fragment = $fragment;
    }

    /**
     * @return string
     */
    public function getAbsoluteUri(): string
    {
        $uri = $this->scheme . '://' . $this->getRawAuthority();

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        if (!empty($this->query)) {
            $uri .= "?{$this->query}";
        }

        if (!empty($this->fragment)) {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function getRawAuthority(): string
    {
        $authority = $this->rawUserInfo ? $this->rawUserInfo . '@' : '';
        $authority .= $this->host;

        if ($this->explicitPortSpecified) {
            $authority .= ":{$this->port}";
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getRelativeUri(): string
    {
        $uri = '';

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        return $uri;
    }

    /**
     * Uses protected user info by default as per rfc3986-3.2.1
     * Uri::getAbsoluteUri() is available if plain-text password information is desirable.
     *
     * @return string
     */
    public function __toString(): string
    {
        $uri = $this->scheme . '://' . $this->getAuthority();

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        if (!empty($this->query)) {
            $uri .= "?{$this->query}";
        }

        if (!empty($this->fragment)) {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    /**
     * Uses protected user info by default as per rfc3986-3.2.1
     * Uri::getRawAuthority() is available if plain-text password information is desirable.
     *
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = $this->userInfo ? $this->userInfo . '@' : '';
        $authority .= $this->host;

        if ($this->explicitPortSpecified) {
            $authority .= ":{$this->port}";
        }

        return $authority;
    }

    /**
     * @param string $var
     * @param string $val
     */
    public function addToQuery(string $var, string $val): void
    {
        if (strlen($this->query) > 0) {
            $this->query .= '&';
        }
        $this->query .= http_build_query([$var => $val], '', '&');
    }

    /**
     * @return bool
     */
    public function hasExplicitTrailingHostSlash(): bool
    {
        return $this->explicitTrailingHostSlash;
    }

    /**
     * @return bool
     */
    public function hasExplicitPortSpecified(): bool
    {
        return $this->explicitPortSpecified;
    }

    /**
     * @param string $rawUserInfo
     *
     * @return string
     */
    protected function protectUserInfo(string $rawUserInfo): string
    {
        $colonPos = strpos($rawUserInfo, ':');

        // rfc3986-3.2.1 | http://tools.ietf.org/html/rfc3986#section-3.2
        // "Applications should not render as clear text any data
        // after the first colon (":") character found within a userinfo
        // subcomponent unless the data after the colon is the empty string
        // (indicating no password)"
        if ($colonPos !== false && strlen($rawUserInfo) - 1 > $colonPos) {
            return substr($rawUserInfo, 0, $colonPos) . ':********';
        }

        return $rawUserInfo;
    }
}
