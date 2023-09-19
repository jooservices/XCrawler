<?php

namespace App\Modules\Client\Services\Flickr\Adapters;

use Illuminate\Support\Collection;

interface ListInterface
{
    public function getList(array $params = []): Collection;

    public function totalPages(): int;

    public function currentPage(): int;
}
