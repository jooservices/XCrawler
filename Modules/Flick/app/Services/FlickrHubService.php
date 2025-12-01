<?php

namespace Modules\Flick\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlickrHubService
{
    protected string $baseUrl;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('flick.hub_url', 'http://localhost:8000/api');
        $this->callbackUrl = config('flick.callback_url', 'http://host.docker.internal/api/flick/callback');
    }

    /**
     * Send a request to FlickrHub.
     *
     * @param string $method Flickr API method (e.g., flickr.contacts.getList)
     * @param array $params Parameters for the API call
     * @return array|null Response from Hub (usually contains request_id)
     */
    public function request(string $method, array $params = []): ?array
    {
        try {
            $response = Http::post("{$this->baseUrl}/flickr/request", [
                'method' => $method,
                'params' => $params,
                'callback_url' => $this->callbackUrl,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("FlickrHub Request Failed: {$method}", [
                'status' => $response->status(),
                'body' => $response->body(),
                'params' => $params,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("FlickrHub Connection Error: {$e->getMessage()}", [
                'method' => $method,
                'params' => $params,
            ]);

            return null;
        }
    }

    /**
     * Get photo sizes directly (synchronous check if needed, though usually via callback).
     * But for download command, we might want a direct call if Hub supports it,
     * OR we use the async flow.
     * The prompt says: "Use flickr.photos.getSizes to getSize".
     * If Hub only supports async via callback, we must handle that.
     * However, for "Download", we need the size URL *now*.
     *
     * Assumption: FlickrHub might support synchronous return if we don't provide callback_url?
     * Or we just use the same async flow and wait?
     *
     * Let's assume for now we use the async flow for everything to be consistent.
     * BUT, for the download command, waiting for a callback is complex.
     *
     * Let's check the openapi.yaml again.
     * It says /flickr/request returns { request_id, status: queued }.
     * So it IS async.
     *
     * So for "Download", we might need to:
     * 1. Send request for getSizes.
     * 2. Wait (poll?) or just have the callback update the DB, and the download command
     *    retries later?
     *
     * The prompt says: "1 command download... Dùng flickr.photos.getSizes để getSize".
     * If the sizes are not in DB, we need to get them.
     *
     * Strategy:
     * The download command iterates photos.
     * If sizes_json is null:
     *    - Trigger getSizes request.
     *    - Skip downloading for now.
     *    - (Next run will pick it up if callback updated it).
     */
}
