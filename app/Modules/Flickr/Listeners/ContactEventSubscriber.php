<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\ContactTasksCreatedEvent;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;

class ContactEventSubscriber
{
    public function onFlickrContactCreated(ContactCreatedEvent $event): void
    {
        foreach (FlickrService::CONTACT_TASKS as $task) {
            $event->contact->tasks()->create([
                'task' => $task,
                'state_code' => States::STATE_INIT,
            ]);
        }

        Event::dispatch(new ContactTasksCreatedEvent());
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ContactCreatedEvent::class,
            [self::class, 'onFlickrContactCreated']
        );
    }
}
