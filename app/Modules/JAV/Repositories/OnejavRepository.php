<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\JAV\Events\Onejav\ItemCreatedEvent;
use App\Modules\JAV\Events\Onejav\ItemUpdatedEvent;
use App\Modules\JAV\Models\Onejav as OnejavModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class OnejavRepository extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(OnejavModel::class));
    }

    public function create(array $attributes): Model
    {
        $item = OnejavModel::updateOrCreate([
            'url' => $attributes['url'],
            'dvd_id' => $attributes['dvd_id']
        ], $attributes);

        if ($item->wasRecentlyCreated) {
            Event::dispatch(new ItemCreatedEvent($item));
            return $item;
        }

        Event::dispatch(new ItemUpdatedEvent($item));

        return $item;
    }
}
