<?php

namespace App\Modules\JAV\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OnejavCompleted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Collection $items)
    {
    }
}
