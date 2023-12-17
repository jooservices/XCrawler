<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav\Entities;

use App\Modules\Core\Entity\BaseEntity;
use Carbon\Carbon;

/**
 * @property string $url
 * @property string $dvd_id
 * @property float $size
 * @property Carbon $date
 * @property array $genres
 * @property array $performers
 * @property string $description
 * @property string $cover
 * @property string $torrent
 * @property array $gallery
 */
class ItemEntity extends BaseEntity
{
}
