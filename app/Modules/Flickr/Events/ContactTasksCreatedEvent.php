<?php

namespace App\Modules\Flickr\Events;

use Illuminate\Queue\SerializesModels;

class ContactTasksCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
}
