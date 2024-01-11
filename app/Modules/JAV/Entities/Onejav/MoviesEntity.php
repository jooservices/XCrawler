<?php

namespace App\Modules\JAV\Entities\Onejav;

use App\Modules\Core\Entities\BaseEntity;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\ItemsEntityInterface;
use Illuminate\Support\Collection;

/**
 * @property int $lastPage
 * @property MovieEntity[]|Collection $items
 */
class MoviesEntity extends BaseEntity implements ItemsEntityInterface
{
}
