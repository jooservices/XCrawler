<?php

namespace App\Modules\JAV\Entities;

use App\Modules\Core\Entities\BaseEntity;
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
class OnejavEntity extends BaseEntity
{
    protected array $fields = [
        'url',
        'dvd_id',
        'size',
        'date',
        'genres',
        'performers',
        'description',
        'cover',
        'torrent',
        'gallery',
    ];

    protected array $casts = [
        'url' => 'string',
        'dvd_id' => 'string',
        'size' => 'float',
        //'date' => 'date',
        'genres' => 'array',
        'performers' => 'array',
        'description' => 'string',
        'cover' => 'string',
        'torrent' => 'string',
        'gallery' => 'array',
    ];
}
