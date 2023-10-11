<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Flickr\Events\BeforeProcessContact;
use Illuminate\Events\Dispatcher;

class ContactEventSubscriber
{

    public function onBeforeProcessContact(BeforeProcessContact $event): void
    {
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            BeforeProcessContact::class,
            [self::class, 'onBeforeProcessContact']
        );
    }
}
