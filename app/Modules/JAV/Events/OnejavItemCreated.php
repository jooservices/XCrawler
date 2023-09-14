<?php

namespace App\Modules\JAV\Events;

use App\Modules\JAV\Models\Onejav;
use Illuminate\Queue\SerializesModels;

class OnejavItemCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Onejav $model)
    {
        //
    }
}
