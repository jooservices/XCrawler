<?php

namespace App\Modules\Core\Repositories;

use AllowDynamicProperties;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Model $model
 */
#[AllowDynamicProperties] class BaseRepository
{
    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }
}
