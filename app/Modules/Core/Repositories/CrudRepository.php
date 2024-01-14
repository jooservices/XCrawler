<?php

namespace App\Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class CrudRepository extends ItemsRepository
{
    public function create(array $attributes): Model
    {
        return $this->getModel()
            ->newQuery()
            ->create(Arr::only($attributes, $this->getColumns()));
    }

    protected function getColumns()
    {
        return Schema::getColumnListing($this->getModel()->getTable());
    }

    public function get(string $uuid): Model
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('uuid', $uuid)->firstOrFail();
    }

    public function update(string $uuid, array $attributes): bool
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('uuid', $uuid)
            ->update(Arr::only($attributes, $this->getColumns()));
    }

    public function delete(string $uuid): bool
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('uuid', $uuid)->delete();
    }
}
