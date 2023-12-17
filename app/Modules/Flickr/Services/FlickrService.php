<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\FetchContactsCompletedEvent;
use App\Modules\Flickr\Jobs\ContactsJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\Flickr\Adapters\Contacts;
use App\Modules\Flickr\Services\Flickr\Adapters\Favorites;
use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\Flickr\Adapters\Photos;
use App\Modules\Flickr\Services\Flickr\Adapters\Photosets;
use Exception;
use Illuminate\Support\Facades\Event;

/**
 * @property Contacts $contacts
 * @property Favorites $favorites
 * @property People $people
 * @property Photos $photos
 * @property Photosets $photosets
 */
class FlickrService
{
    public const TASK_CONTACT_FAVORITES = 'contact-favorites';
    public const TASK_CONTACT_PHOTOS = 'contact-photos';
    public const TASK_PHOTOSETS = 'photosets';
    public const TASK_PHOTOSET_PHOTOS = 'photoset-photos';

    public const  TASKS = [
        self::TASK_CONTACT_FAVORITES,
        self::TASK_CONTACT_PHOTOS,
        self::TASK_PHOTOSETS,
        self::TASK_PHOTOSET_PHOTOS
    ];

    public const SERVICE_NAME = 'flickr';
    public const QUEUE_NAME = 'flickr';

    private Flickr $provider;
    private Integration $integration;


    public function setIntegration(Integration $integration): self
    {
        $this->integration = $integration;
        $provider = app(Flickr::class);
        /**
         * @phpstan-ignore-next-line
         */
        $this->provider = app(ProviderFactory::class)->oauth1($provider, $this->integration);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function __get(string $name): mixed
    {
        if (!isset($this->provider)) {
            throw new Exception('Provider is not loaded');
        }

        $className = 'App\\Modules\\Flickr\\Services\\Flickr\\Adapters\\' . ucfirst($name);
        if (!class_exists($className)) {
            throw new Exception('Adapter not found');
        }

        return app($className, ['provider' => $this->provider]);
    }
}
