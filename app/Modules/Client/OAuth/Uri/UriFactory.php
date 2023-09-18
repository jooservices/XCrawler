<?php

namespace App\Modules\Client\OAuth\Uri;

use RuntimeException;

/**
 * Factory class for uniform resource indicators.
 * @SuppressWarnings(PHPMD)
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * Factory method to build a URI from a super-global $_SERVER array.
     *
     * @return UriInterface
     */
    public function createFromSuperGlobalArray(array $server): Uri|UriInterface
    {
        if ($uri = $this->attemptProxyStyleParse($server)) {
            return $uri;
        }

        $scheme = $this->detectScheme($server);
        $host = $this->detectHost($server);
        $port = $this->detectPort($server);
        $path = $this->detectPath($server);
        $query = $this->detectQuery($server);

        return $this->createFromParts($scheme, '', $host, $port, $path, $query);
    }

    /**
     * @param string $absoluteUri
     *
     * @return UriInterface
     */
    public function createFromAbsolute(string $absoluteUri): UriInterface
    {
        return new Uri($absoluteUri);
    }

    /**
     * Factory method to build a URI from parts.
     *
     * @param string $scheme
     * @param string $userInfo
     * @param string $host
     * @param string $port
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
    ): UriInterface {
        $uri = new Uri();
        $uri->setScheme($scheme);
        $uri->setUserInfo($userInfo);
        $uri->setHost($host);
        $uri->setPort($port);
        $uri->setPath($path);
        $uri->setQuery($query);
        $uri->setFragment($fragment);

        return $uri;
    }

    /**
     * @param array $server
     *
     * @return null|UriInterface
     */
    private function attemptProxyStyleParse(array $server)
    {
        // If the raw HTTP request message arrives with a proxy-style absolute URI in the
        // initial request line, the absolute URI is stored in $_SERVER['REQUEST_URI'] and
        // we only need to parse that.
        if (isset($server['REQUEST_URI']) && parse_url($server['REQUEST_URI'], PHP_URL_SCHEME)) {
            return new Uri($server['REQUEST_URI']);
        }

        return null;
    }

    /**
     * @param array $server
     *
     * @return string
     */
    private function detectPath(array $server)
    {
        if (!isset($server['REQUEST_URI']) || !isset($server['REDIRECT_URL'])) {
            throw new RuntimeException('Could not detect URI path from superglobal');
        }

        $uri = $server['REQUEST_URI'] ?? $server['REDIRECT_URL'];

        $queryStr = strpos($uri, '?');
        if ($queryStr !== false) {
            $uri = substr($uri, 0, $queryStr);
        }

        return $uri;
    }

    /**
     * @return string
     */
    private function detectHost(array $server)
    {
        $host = $server['HTTP_HOST'] ?? '';

        if (str_contains($host, ':')) {
            $host = parse_url($host, PHP_URL_HOST);
        }

        return $host;
    }

    /**
     * @return string
     */
    private function detectPort(array $server)
    {
        return $server['SERVER_PORT'] ?? 80;
    }

    /**
     * @return string
     */
    private function detectQuery(array $server)
    {
        return $server['QUERY_STRING'] ?? '';
    }

    /**
     * Determine URI scheme component from superglobal array.
     *
     * When using ISAPI with IIS, the value will be "off" if the request was
     * not made through the HTTPS protocol. As a result, we filter the
     * value to a bool.
     *
     * @param array $server A super-global $_SERVER array
     *
     * @return string Returns http or https depending on the URI scheme
     */
    private function detectScheme(array $server)
    {
        if (isset($server['HTTPS']) && filter_var($server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
            return 'https';
        }

        return 'http';
    }
}
