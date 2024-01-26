<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Flickr\Events\Exceptions\PhotosetNotFoundEvent;
use App\Modules\Flickr\Events\Exceptions\UserNotFoundEvent;
use Illuminate\Events\Dispatcher;

class ExceptionsEventSubscriber
{
    public function onPhotosetNotFound(PhotosetNotFoundEvent $event)
    {
        // Delete all tasks belongs to this photoset
        $event->photoset->tasks()->delete();
        $event->photoset->delete();
    }

    public function onUserNotFound(UserNotFoundEvent $event)
    {
        // Delete all tasks belongs to this user
        $event->contact->tasks()->delete();
        // Soft delete the contact
        $event->contact->delete();
        $event->contact->tasks()->delete();
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            PhotosetNotFoundEvent::class,
            [self::class, 'onPhotosetNotFound']
        );

        $events->listen(
            UserNotFoundEvent::class,
            [self::class, 'onUserNotFound']
        );
    }
}
