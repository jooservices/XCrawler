<?php

namespace App\Modules\Flickr\Listeners;

use App\Modules\Flickr\Events\BeforeProcessContact;
use Exception;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;

class ContactEventSubscriber
{
    public function onBeforeProcessContact(): void
    {
        if (Cache::get('flickr_requests_count', 1) >= 3500) {
            throw new Exception('Flickr API limit reached.');
        }
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            BeforeProcessContact::class,
            [self::class, 'onBeforeProcessContact']
        );
    }
}
