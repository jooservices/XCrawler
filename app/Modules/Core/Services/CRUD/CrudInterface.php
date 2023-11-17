<?php

namespace App\Modules\Core\Services\CRUD;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CrudInterface
{
    public function create(array $properties): Model;

    public function get(string $uuid): Model;

    public function list(Collection $payload): Collection;

    public function update(string $uuid, array $properties): bool;

    public function delete(string $uuid): bool;
}
