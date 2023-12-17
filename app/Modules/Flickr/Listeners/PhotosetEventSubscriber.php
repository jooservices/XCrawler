<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Events\Dispatcher;

class PhotosetEventSubscriber
{
    public function onPhotosetCreated(PhotosetCreatedEvent $event): void
    {
        $event->photoset->tasks()->create([
            'task' => FlickrService::TASK_PHOTOSET_PHOTOS,
            'state_code' => States::STATE_INIT,
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            PhotosetCreatedEvent::class,
            [self::class, 'onPhotosetCreated']
        );
    }
}
