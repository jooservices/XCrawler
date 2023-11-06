<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Repositories\CrudRepository;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractCrudService extends AbstractBaseService
{
    abstract protected function getRepository(): CrudRepository;

    public function create(array $properties): Model
    {
        return $this->getRepository()->create($properties);
    }
}
