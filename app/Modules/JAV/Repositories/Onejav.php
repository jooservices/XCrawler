<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\JAV\Events\OnejavItemCreated;
use App\Modules\JAV\Events\OnejavItemUpdated;
use App\Modules\JAV\Models\Onejav as OnejavModel;
use Illuminate\Support\Facades\Event;

class Onejav
{
    public function create(array $attributes)
    {
        $item = OnejavModel::firstOrCreate([
            'url' => $attributes['url'],
            'dvd_id' => $attributes['dvd_id']
        ], $attributes);

        if ($item->wasRecentlyCreated) {
            Event::dispatch(new OnejavItemCreated($item));

            return;
        }

        Event::dispatch(new OnejavItemUpdated($item));
    }
}
