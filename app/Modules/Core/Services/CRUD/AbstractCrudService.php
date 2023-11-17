<?php

namespace App\Modules\Core\Services\CRUD;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\Core\Services\AbstractBaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractCrudService extends AbstractBaseService implements CrudInterface
{
    abstract protected function getRepository(): CrudRepository;

    public function create(array $properties): Model
    {
        return $this->getRepository()->create($properties);
    }

    public function get(string $uuid): Model
    {
        return $this->getRepository()->get($uuid);
    }

    public function list(Collection $payload): Collection
    {
        return $this->getRepository()->items($payload);
    }

    public function update(string $uuid, array $properties): bool
    {
        return $this->getRepository()->update($uuid, $properties);
    }

    public function delete(string $uuid): bool
    {
        return $this->getRepository()->delete($uuid);
    }
}
