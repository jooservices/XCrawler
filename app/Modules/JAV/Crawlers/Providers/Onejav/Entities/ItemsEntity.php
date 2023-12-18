<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav\Entities;

use App\Modules\Core\Entities\BaseEntity;
use App\Modules\JAV\Entities\OnejavEntity;
use Illuminate\Support\Collection;

/**
 * @property int $lastPage
 * @property OnejavEntity[]|Collection $items
 */
class ItemsEntity extends BaseEntity implements ItemsEntityInterface
{
}
