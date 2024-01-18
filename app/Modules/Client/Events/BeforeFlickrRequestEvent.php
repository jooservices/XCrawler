<?php

namespace App\Modules\Client\Events;

use Illuminate\Queue\SerializesModels;

class BeforeFlickrRequestEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}
