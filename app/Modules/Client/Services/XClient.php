<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Models\RequestLog;
use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\Core\Traits\HasOptions;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class XClient
{
    use HasOptions;

    private RequestLog $requestLog;

    private string $contentType = 'application/x-www-form-urlencoded';

    private Client $client;

    public function __construct()
    {
        $this->setOptions([
            'verify' => false,
        ]);

        $this->init();
    }

    protected function init(): void
    {
        $this->client = app(Factory::class)
            ->enableRetries()
            ->make();
    }

    public function get(string $endpoint, array $payload = [], array $options = []): ?XResponseInterface
    {
        return $this->request(
            __FUNCTION__,
            $endpoint,
            array_merge($this->buildPayload(__FUNCTION__, $payload), $options)
        );
    }

    /**
     * @phpcs:disable
     *
     * @throws GuzzleException
     */
    public function request(string $method, string $endpoint, array $options = []): ?XResponseInterface
    {
        $method = strtoupper($method);
        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
            ]
        );

        $this->requestLog = RequestLog::create([
            'method' => $method,
            'url' => $endpoint,
            'options' => $options,
            'started_at' => Carbon::now(),
            'is_success' => false,
        ]);

        $xresponse = new XResponse();


        $key = md5(serialize([$method, $endpoint, $options]));

        $xresponse = Cache::remember($key, 60 * 60, function () use ($method, $endpoint, $options, $xresponse) {
            $response = $this->client->request($method, $endpoint, $options);
            $xresponse->setResponse($response);

            return $xresponse;
        });

        $this->requestLog->update([
            'status_code' => $xresponse->getStatusCode(),
            'response' => $xresponse->getResponse(),
            'is_success' => $xresponse->isSuccessful(),
            'completed_at' => Carbon::now(),
        ]);

        return $xresponse;
    }

    private function buildPayload(string $method, array $payload): array
    {
        $payload = $this->convertToUTF8($payload);

        if (strtoupper($method) == 'GET') {
            return ['query' => $payload];
        }

        if ($this->contentType == 'application/json') {
            return ['json' => $payload];
        }

        return ['form_params' => $payload];
    }

    /**
     * Sanitize payload to UTF-8.
     */
    protected function convertToUTF8(array $array): array
    {
        array_walk_recursive($array, function (&$item) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $array;
    }

    /**
     * Set the content type.
     *
     *
     * @return $this
     */
    public function setContentType(string $contentType = 'json'): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function post(string $endpoint, array $payload = [], array $options = []): ?XResponseInterface
    {
        return $this->request(
            __FUNCTION__,
            $endpoint,
            array_merge($this->buildPayload(__FUNCTION__, $payload), $options)
        );
    }

    public function put(string $endpoint, array $payload = [], array $options = []): ?XResponseInterface
    {
        return $this->request(
            __FUNCTION__,
            $endpoint,
            array_merge($this->buildPayload(__FUNCTION__, $payload), $options)
        );
    }

    public function patch(string $endpoint, array $payload = [], array $options = []): ?XResponseInterface
    {
        return $this->request(
            __FUNCTION__,
            $endpoint,
            array_merge($this->buildPayload(__FUNCTION__, $payload), $options)
        );
    }

    public function delete(string $endpoint, array $payload = [], array $options = []): ?XResponseInterface
    {
        return $this->request(
            __FUNCTION__,
            $endpoint,
            array_merge($this->buildPayload(__FUNCTION__, $payload), $options)
        );
    }
}
