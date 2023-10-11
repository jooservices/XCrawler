<?php

namespace App\Modules\Flickr\Providers;

use App\Modules\Flickr\Listeners\ContactEventSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        ContactEventSubscriber::class
    ];
}
