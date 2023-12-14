<?php

namespace App\Modules\Core\Events;

use Illuminate\Queue\SerializesModels;

class TaskCreatedEvent
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
