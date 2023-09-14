<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\JAV\Events\OnejavItemCreated;
use App\Modules\JAV\Events\OnejavItemUpdated;
use App\Modules\JAV\Models\Onejav as OnejavModel;
use Illuminate\Support\Facades\Event;

class Onejav extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(OnejavModel::class));
    }

    public function updateOrCreate(array $attributes)
    {
        $item = OnejavModel::updateOrCreate([
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
