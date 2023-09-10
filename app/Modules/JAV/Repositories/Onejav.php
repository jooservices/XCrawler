<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\JAV\Models\Onejav as OnejavModel;

class Onejav
{
    public function create(array $attributes)
    {
        OnejavModel::firstOrCreate([
            'url' => $attributes['url'],
            'dvd_id' => $attributes['dvd_id']
        ], $attributes);
    }
}
