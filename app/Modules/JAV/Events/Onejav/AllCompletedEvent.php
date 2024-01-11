<?php

namespace App\Modules\JAV\Events\Onejav;

use Illuminate\Queue\SerializesModels;

class AllCompletedEvent
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
