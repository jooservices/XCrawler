<?php

namespace App\Modules\JAV\Repositories;

use App\Modules\Core\Repositories\ItemsRepository;
use App\Modules\JAV\Models\MoviePerformer;

class IdolRepository extends ItemsRepository
{
    public function __construct(protected MoviePerformer $model)
    {
    }
}
