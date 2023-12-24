<?php

namespace App\Modules\JAV\Events\Onejav;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyCompletedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Carbon $date, public Collection $items)
    {
        //
    }
}
