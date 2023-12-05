<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\ItemsRepository;
use App\Modules\JAV\Models\MovieGenre;

class GenreRepository extends ItemsRepository
{
    public function __construct(protected MovieGenre $model)
    {
    }
}
