<?php

namespace App\Modules\JAV\Entities\Onejav;

use App\Modules\Core\Entities\BaseEntity;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\ItemsEntityInterface;
use Illuminate\Support\Collection;

/**
 * @property int $lastPage
 * @property TagEntity[]|Collection $items
 */
class TagsEntity extends BaseEntity implements ItemsEntityInterface
{
}
