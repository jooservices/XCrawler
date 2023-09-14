<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\JAV\Events\OnejavItemCreated;
use App\Modules\JAV\Events\OnejavItemUpdated;
use App\Modules\JAV\Models\Onejav as OnejavModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;

class Onejav extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(OnejavModel::class));
    }

    public function firstOrCreate(array $attributes)
    {
        $item = OnejavModel::firstOrCreate([
            'url' => $attributes['url'],
            'dvd_id' => $attributes['dvd_id']
        ], Arr::only($attributes, $this->getColumns()));

        if ($item->wasRecentlyCreated) {
            Event::dispatch(new OnejavItemCreated($item));

            return;
        }

        Event::dispatch(new OnejavItemUpdated($item));
    }
}
