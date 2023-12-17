<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav\Entities;

use App\Modules\Core\Entity\BaseEntity;
use Illuminate\Support\Collection;

/**
 * @property int $lastPage
 * @property ItemEntity[]|Collection $items
 */
class ItemsEntity extends BaseEntity implements ItemsEntityInterface
{
}
