<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\Core\Traits\HasProperties;
use Carbon\Carbon;

/**
* @property string|null $url
 * @property string|null $cover
 * @property string|null $title
 * @property string|null $code
 * @property Carbon|null $date
 * @property string|null $studio
 * @property string|null $description
 * @property string|null $dvd_id
 * @property array $genres
 * @property array $performers
 * @property string $director
 * @property array $gallery
 * @property float $size
 * @property string $torrent
 */
class Entity
{
    use HasProperties;

    public function __construct()
    {
        $this->bootHasProperties();
    }
}
