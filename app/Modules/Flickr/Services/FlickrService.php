<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Flickr\Exceptions\AdapterNotFound;
use App\Modules\Flickr\Exceptions\IntegrationNotFoundException;
use App\Modules\Flickr\Services\Flickr\Adapters\Contacts;
use App\Modules\Flickr\Services\Flickr\Adapters\Favorites;
use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\Flickr\Adapters\Photos;
use App\Modules\Flickr\Services\Flickr\Adapters\Photosets;
use Exception;

/**
 * @property Contacts $contacts
 * @property Favorites $favorites
 * @property People $people
 * @property Photos $photos
 * @property Photosets $photosets
 */
class FlickrService
{
    public const SERVICE_NAME = 'flickr';
    public const QUEUE_NAME = 'flickr';

    private Flickr $provider;
    private Integration $integration;

    public function setIntegration(Integration $integration): self
    {
        $this->integration = $integration;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function __get(string $name): mixed
    {
        if (!isset($this->integration)) {
            $this->integration = app(IntegrationRepository::class)
                ->getCompleted(self::SERVICE_NAME)
                ->first();
            if (!$this->integration) {
                throw new IntegrationNotFoundException('Integration not found');
            }
        }

        $provider = app(Flickr::class);
        /**
         * @phpstan-ignore-next-line
         */
        $this->provider = app(ProviderFactory::class)->oauth1($provider, $this->integration);

        $className = 'App\\Modules\\Flickr\\Services\\Flickr\\Adapters\\' . ucfirst($name);
        if (!class_exists($className)) {
            throw new AdapterNotFound('Adapter not found');
        }

        return app($className, ['provider' => $this->provider]);
    }
}
