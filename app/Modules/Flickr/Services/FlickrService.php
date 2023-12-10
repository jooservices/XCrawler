<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\Flickr\Adapters\Contacts;
use App\Modules\Flickr\Services\Flickr\Adapters\Favorites;
use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\Flickr\Adapters\Photos;
use Exception;
use Illuminate\Support\Facades\Event;

/**
 * @property Contacts $contacts
 * @property Favorites $favorites
 * @property People $people
 * @property Photos $photos
 */
class FlickrService
{
    public const TASK_CONTACT_FAVORITES = 'contact-favorites';
    public const TASK_CONTACT_PHOTOS = 'contact-photos';

    public const TASKS = [
        self::TASK_CONTACT_FAVORITES,
        self::TASK_CONTACT_PHOTOS,
    ];

    public const SERVICE_NAME = 'flickr';
    public const QUEUE_NAME = 'flickr';

    private Flickr $provider;
    private Integration $integration;

    /**
     * @throws Exception
     */
    public function setIntegration(Integration $integration): self
    {
        $this->integration = $integration;
        $provider = app(Flickr::class);
        /**
         * @phpstan-ignore-next-line
         */
        $this->provider = app(ProviderFactory::class)->oauth1($provider, $integration);

        return $this;
    }

    public function processContacts(int $page = 1): void
    {
        $contactsService = $this->contacts;
        $contactsService->getList(['page' => $page])->each(function ($contact) {
            $this->create($contact);
        });

        if ($page === $contactsService->totalPages()) {
            return;
        }

        ContactJob::dispatch($this->integration, $page + 1)->onQueue('flickr');
    }

    public function create(array $contact): FlickrContact
    {
        $contact = app(ContactRepository::class)->create($contact);

        if ($contact->wasRecentlyCreated) {
            Event::dispatch(new ContactCreatedEvent($contact));
        }

        return $contact;
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
