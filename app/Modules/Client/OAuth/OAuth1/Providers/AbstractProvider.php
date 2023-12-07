<?php

namespace App\Modules\Client\OAuth\OAuth1\Providers;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\AbstractBaseProvider;
use App\Modules\Client\OAuth\Events\RetrievedRequestToken;
use App\Modules\Client\OAuth\Exceptions\RequestLimited;
use App\Modules\Client\OAuth\Exceptions\TokenResponseException;
use App\Modules\Client\OAuth\OAuth1\Signature\Signature;
use App\Modules\Client\OAuth\OAuth1\Signature\SignatureInterface;
use App\Modules\Client\OAuth\OAuth1\Token\TokenInterface;
use App\Modules\Client\OAuth\Storage\TokenStorageInterface;
use App\Modules\Client\OAuth\Uri\UriInterface;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\Client\Services\XClient;
use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\States;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

/**
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractProvider extends AbstractBaseProvider implements ProviderInterface
{
    public const SIGNATURE_METHOD = 'HMAC-SHA1';

    protected SignatureInterface $signature;


    public function __construct(
        protected TokenStorageInterface $storage,
        protected XClient $client,
        protected ?UriInterface $baseApiUri = null
    ) {
        parent::__construct($this->storage, $this->client);

        $this->signature = app(Signature::class, ['credentials' => $this->credentials]);
        $this->signature->setHashingAlgorithm($this->getSignatureMethod());
    }

    /**
     * {@inheritdoc}
     */
    public function requestRequestToken(): TokenInterface
    {
        $authorizationHeader = ['Authorization' => $this->buildAuthorizationHeaderForTokenRequest()];
        $headers = [...$authorizationHeader, ...$this->getExtraOAuthHeaders()];

        $token = $this->parseRequestTokenResponse(
            $this->client->post(
                $this->getRequestTokenEndpoint(),
                [],
                [
                    'headers' => $headers
                ]
            )->getBody()
        );

        $this->storage->storeAccessToken($this->service(), $token);

        Event::dispatch(new RetrievedRequestToken($token));

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUri(array $additionalParameters = []): UriInterface
    {
        // Build the url
        $url = clone $this->getAuthorizationEndpoint();
        foreach ($additionalParameters as $key => $val) {
            $url->addToQuery($key, $val);
        }

        return $url;
    }

    public function retrieveAccessToken(string $verifier, ?TokenInterface $requestToken = null): TokenInterface
    {
        $token = $this->storage->retrieveAccessToken($this->service());

        // If no request token is provided, try to get it from this object.
        if ($requestToken === null) {
            $requestToken = $token->getAccessToken();
        }

        $accessToken = $this->requestAccessToken($requestToken, $verifier, $token->getAccessTokenSecret());

        if ($accessToken) {
            Integration::where('service', $this->service())
                ->where('state_code', States::STATE_INIT)
                ->update([
                'token_secret' => $accessToken->getAccessTokenSecret(),
                'token' => $accessToken->getAccessToken(),
                'state_code' => States::STATE_COMPLETED
            ]);
        }

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function requestAccessToken($token, $verifier, $tokenSecret = null): TokenInterface
    {
        if ($tokenSecret === null) {
            $storedRequestToken = $this->storage->retrieveAccessToken($this->service());
            $tokenSecret = $storedRequestToken->getRequestTokenSecret();
        }
        $this->signature->setTokenSecret($tokenSecret);

        $bodyParams = [
            'oauth_verifier' => $verifier,
        ];

        $authorizationHeader = [
            'Authorization' => $this->buildAuthorizationHeaderForAPIRequest(
                'POST',
                $this->getAccessTokenEndpoint(),
                $this->storage->retrieveAccessToken($this->service()),
                $bodyParams
            ),
        ];

        $responseBody = $this->client->post(
            $this->getAccessTokenEndpoint(),
            $bodyParams,
            [
                'headers' => array_merge($authorizationHeader, $this->getExtraOAuthHeaders())
            ]
        );

        if (!$responseBody->isSuccessful()) {
            throw  new TokenResponseException($responseBody->getBody());
        }

        $token = $this->parseAccessTokenResponse($responseBody->getBody());

        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }

    /**
     * Refreshes an OAuth1 access token.
     *
     * @return TokenInterface $token
     */
    public function refreshAccessToken(TokenInterface $token): TokenInterface
    {
        /**
         * Do nothing, as refresh tokens are not supported in OAuth1.
         */
        return $token;
    }

    /**
     * Sends an authenticated API request to the path provided.
     * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
     *
     * @param string|UriInterface $path
     * @param string $method HTTP method
     * @param array $body Request body if applicable (key/value pairs)
     * @param array $extraHeaders Extra headers if applicable.
     *                                          These will override service-specific any defaults.
     *
     * @return XResponseInterface
     * @throws \Exception
     */
    public function request(
        $path,
        array $body = [],
        array $extraHeaders = [],
        string $method = 'GET'
    ): XResponseInterface {
        $uri = $this->determineRequestUriFromPath($path, $this->baseApiUri);

        $token = $this->storage->retrieveAccessToken($this->service());
        $extraHeaders = array_merge($this->getExtraApiHeaders(), $extraHeaders);
        $authorizationHeader = [
            'Authorization' => $this->buildAuthorizationHeaderForAPIRequest($method, $uri, $token, $body),
        ];

        $method = strtolower($method);
        $response = $this->client->{$method}(
            $uri,
            $body,
            [
                'headers' => array_merge($authorizationHeader, $extraHeaders),
            ]
        );

        return $response;
    }

    /**
     * Return any additional headers always needed for this service implementation's OAuth calls.
     *
     * @return array
     */
    protected function getExtraOAuthHeaders(): array
    {
        return [];
    }

    /**
     * Return any additional headers always needed for this service implementation's API calls.
     *
     * @return array
     */
    protected function getExtraApiHeaders(): array
    {
        return [];
    }

    /**
     * Builds the authorization header for getting an access or request token.
     *
     * @return string
     */
    protected function buildAuthorizationHeaderForTokenRequest(array $extraParameters = []): string
    {
        $parameters = [...$this->getBasicAuthorizationHeaderInfo(), ...$extraParameters];
        $parameters['oauth_signature'] = $this->signature->getSignature(
            $this->getRequestTokenEndpoint(),
            $parameters,
        );

        $authorizationHeader = 'OAuth ';
        $delimiter = '';
        foreach ($parameters as $key => $value) {
            $authorizationHeader .= $delimiter . rawurlencode($key) . '="' . rawurlencode($value) . '"';

            $delimiter = ', ';
        }

        return $authorizationHeader;
    }

    /**
     * Builds the authorization header for an authenticated API request.
     *
     * @param string $method
     * @param UriInterface $uri The uri the request is headed
     * @param array $bodyParams Request body if applicable (key/value pairs)
     *
     * @return string
     */
    protected function buildAuthorizationHeaderForAPIRequest(
        $method,
        UriInterface $uri,
        TokenInterface $token,
        array $bodyParams = null
    ): string {
        $this->signature->setTokenSecret($token->getAccessTokenSecret());
        $authParameters = $this->getBasicAuthorizationHeaderInfo();
        if (isset($authParameters['oauth_callback'])) {
            unset($authParameters['oauth_callback']);
        }

        $authParameters = array_merge($authParameters, ['oauth_token' => $token->getAccessToken()]);

        $signatureParams = (is_array($bodyParams)) ? array_merge($authParameters, $bodyParams) : $authParameters;
        $authParameters['oauth_signature'] = $this->signature->getSignature($uri, $signatureParams, $method);

        if (is_array($bodyParams) && isset($bodyParams['oauth_session_handle'])) {
            $authParameters['oauth_session_handle'] = $bodyParams['oauth_session_handle'];
            unset($bodyParams['oauth_session_handle']);
        }

        $authorizationHeader = 'OAuth ';
        $delimiter = '';

        foreach ($authParameters as $key => $value) {
            $authorizationHeader .= $delimiter . rawurlencode($key) . '="' . rawurlencode($value) . '"';
            $delimiter = ', ';
        }

        return $authorizationHeader;
    }

    /**
     * Builds the authorization header array.
     *
     * @return array
     */
    protected function getBasicAuthorizationHeaderInfo()
    {
        $dateTime = new DateTime();
        $headerParameters = [
            'oauth_callback' => $this->credentials->getCallbackUrl(),
            'oauth_consumer_key' => $this->credentials->getConsumerId(),
            'oauth_nonce' => Str::random(32),
            'oauth_signature_method' => $this->getSignatureMethod(),
            'oauth_timestamp' => $dateTime->format('U'),
            'oauth_version' => $this->getVersion(),
        ];

        return $headerParameters;
    }

    /**
     * @return string
     */
    protected function getSignatureMethod(): string
    {
        return self::SIGNATURE_METHOD;
    }

    /**
     * This returns the version used in the authorization header of the requests.
     *
     * @return string
     */
    protected function getVersion(): string
    {
        return '1.0';
    }

    /**
     * Parses the request token response and returns a TokenInterface.
     * This is only needed to verify the `oauth_callback_confirmed` parameter. The actual
     * parsing logic is contained in the access token parser.
     *
     * @abstract
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     */
    abstract protected function parseRequestTokenResponse(string $responseBody): TokenInterface;

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     * @abstract
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     */
    abstract protected function parseAccessTokenResponse(string $responseBody): TokenInterface;
}
