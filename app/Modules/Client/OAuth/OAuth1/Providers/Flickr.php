<?php

namespace App\Modules\Client\OAuth\OAuth1\Providers;

use App\Modules\Client\Events\AfterFlickrRequest;
use App\Modules\Client\Events\BeforeFlickrRequest;
use App\Modules\Client\OAuth\Exceptions\TokenResponseException;
use App\Modules\Client\OAuth\OAuth1\Token\Token;
use App\Modules\Client\OAuth\OAuth1\Token\TokenInterface;
use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use App\Modules\Client\OAuth\Uri\Uri;
use App\Modules\Client\OAuth\Uri\UriInterface;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\Client\Services\XClient;
use Illuminate\Support\Facades\Event;

class Flickr extends AbstractProvider
{
    public const PROVIDER_NAME = 'flickr';
    public const OAUTH_REQUEST_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/request_token';
    public const OAUTH_AUTHORIZATION_ENDPOINT = 'https://www.flickr.com/services/oauth/authorize';
    public const OAUTH_REST_ENDPOINT = 'https://www.flickr.com/services/rest/';
    public const OAUTH_ACCESS_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/access_token';
    private string $format = 'json';

    public function __construct(
        protected TokenStorageInterface $storage,
        protected XClient $client,
        ?UriInterface $baseApiUri = null
    ) {
        parent::__construct($storage, $client, $baseApiUri);

        if ($baseApiUri === null) {
            $this->baseApiUri = new Uri(self::OAUTH_REST_ENDPOINT);
        }
    }

    public function getRequestTokenEndpoint(): UriInterface
    {
        return new Uri(self::OAUTH_REQUEST_TOKEN_ENDPOINT);
    }

    public function getAuthorizationEndpoint(): UriInterface
    {
        return new Uri(self::OAUTH_AUTHORIZATION_ENDPOINT);
    }

    public function getAccessTokenEndpoint(): UriInterface
    {
        return new Uri(self::OAUTH_ACCESS_TOKEN_ENDPOINT);
    }

    protected function parseRequestTokenResponse(string $responseBody): TokenInterface
    {
        parse_str($responseBody, $data);

        if (!is_array($data)) {
            //throw new TokenResponseException('Unable to parse response.');
        } elseif (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] != 'true') {
            //throw new TokenResponseException('Error in retrieving token.');
        }

        return $this->parseAccessTokenResponse($responseBody);
    }

    protected function parseAccessTokenResponse(string $responseBody): TokenInterface
    {
        parse_str($responseBody, $data);

        if (!is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        }

        if (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = app(Token::class);
        $token->setRequestToken($data['oauth_token']);
        $token->setRequestTokenSecret($data['oauth_token_secret']);
        $token->setAccessToken($data['oauth_token']);
        $token->setAccessTokenSecret($data['oauth_token_secret']);
        $token->setEndOfLife(Token::EOL_NEVER_EXPIRES);
        unset($data['oauth_token'], $data['oauth_token_secret']);
        $token->setExtraParams($data);

        return $token;
    }

    /**
     * @param $path
     * @param string $method
     * @param array $body
     * @param array $extraHeaders
     * @return XResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(
        $path,
        array $body = [],
        array $extraHeaders = [],
        string $method = 'POST',
    ): XResponseInterface {
        Event::dispatch(new BeforeFlickrRequest());

        $uri = $this->determineRequestUriFromPath('/', $this->baseApiUri);
        $uri->addToQuery('method', $path);

        if (!empty($this->format)) {
            $uri->addToQuery('format', $this->format);

            if ($this->format === 'json') {
                $uri->addToQuery('nojsoncallback', 1);
            }
        }

        $token = $this->storage->retrieveAccessToken($this->service());

        $extraHeaders = [...$this->getExtraApiHeaders(), ...$extraHeaders];
        $authorizationHeader = [
            'Authorization' => $this->buildAuthorizationHeaderForAPIRequest($method, $uri, $token, $body),
        ];

        $method = strtolower($method);
        $response = $this->client->{$method}(
            $uri,
            $body,
            [
                'headers' => array_merge($authorizationHeader, $extraHeaders)
            ]
        );

        Event::dispatch(new AfterFlickrRequest());

        return $response;
    }

    public function requestRest($path, $body = null, array $extraHeaders = [], $method = 'POST',)
    {
        return $this->request($path, $body, $extraHeaders, $method);
    }

    public function requestXmlrpc($path, $body = null, array $extraHeaders = [], $method = 'POST',)
    {
        $this->format = 'xmlrpc';

        return $this->request($path, $body, $extraHeaders, $method);
    }

    public function requestSoap($path, $body = [], array $extraHeaders = [], $method = 'POST')
    {
        $this->format = 'soap';

        return $this->request($path, $body, $extraHeaders, $method);
    }

    public function requestJson($path, $body = [], array $extraHeaders = [], $method = 'POST')
    {
        $this->format = 'json';

        return $this->request($path, $body, $extraHeaders, $method);
    }

    public function requestPhp($path, $body = [], array $extraHeaders = [], $method = 'POST')
    {
        $this->format = 'php_serial';

        return $this->request($path, $body, $extraHeaders, $method);
    }

    public function service(): string
    {
        return self::PROVIDER_NAME;
    }
}
