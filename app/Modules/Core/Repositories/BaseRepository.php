<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Repositories\Interfaces\HasModelInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements HasModelInterface
{
    protected Model $model;

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
