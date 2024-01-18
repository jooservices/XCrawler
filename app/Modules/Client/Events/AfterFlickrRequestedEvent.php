<?php

namespace App\Modules\Client\Events;

use Illuminate\Queue\SerializesModels;

class AfterFlickrRequestedEvent
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
