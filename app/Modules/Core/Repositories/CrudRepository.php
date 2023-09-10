<?php

namespace App\Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class CrudRepository
{
    protected Model $model;

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function create(array $attributes): Model
    {
        return $this->getModel()->create(Arr::only($attributes, $this->getColumns()));
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    protected function getColumns()
    {
        return Schema::getColumnListing($this->getModel()->getTable());
    }

    public function update(array $attributes): bool
    {
        return $this->getModel()->update(Arr::only($attributes, $this->getColumns()));
    }

    public function delete(): bool
    {
        return $this->getModel()->delete();
    }
}
