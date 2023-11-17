<?php

namespace App\Modules\Core\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface HasModelInterface
{
    public function getModel(): Model;

    public function setModel(Model $model): self;
}
