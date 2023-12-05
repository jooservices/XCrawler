<?php

namespace App\Modules\Core\Repositories;

use AllowDynamicProperties;
use App\Modules\Core\Repositories\Interfaces\HasModelInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Model $model
 */
#[AllowDynamicProperties] class BaseRepository implements HasModelInterface
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
