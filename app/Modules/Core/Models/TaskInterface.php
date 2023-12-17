<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface TaskInterface
{
    public function tasks(): MorphMany;
}
