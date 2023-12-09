<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use Illuminate\Support\Collection;

interface ListInterface
{
    public function getList(array $params = []): Collection;

    public function endOfList(): bool;

    public function totalPages(): int;

    public function currentPage(): int;
}
