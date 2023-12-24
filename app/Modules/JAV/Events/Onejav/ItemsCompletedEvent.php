<?php

namespace App\Modules\JAV\Events\Onejav;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ItemsCompletedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Collection $items)
    {
    }
}
