<?php

namespace App\Modules\Core\Models;

use Jenssegers\Mongodb\Relations\MorphMany;

interface HasTasksInterface
{
    public function tasks(): MorphMany;
}
