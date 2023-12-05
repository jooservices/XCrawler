<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\ItemsRepository;
use App\Modules\JAV\Models\Movie;

class MovieRepository extends ItemsRepository
{
    public function __construct(protected Movie $model)
    {
    }
}
