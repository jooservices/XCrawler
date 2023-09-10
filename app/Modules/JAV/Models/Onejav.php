<?php

namespace App\Modules\JAV\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Onejav extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'onejav';

    protected $fillable = [
        'url',
        'cover',
        'dvd_id',
        'size',
        'date',
        'genres',
        'description',
        'performers',
        'torrent',
        'gallery'
    ];

    protected $casts = [
        'url' => 'string',
        'cover' => 'string',
        'dvd_id' => 'string',
        'size' => 'float',
        'date' => 'date:Y-m-d',
        'genres' => 'array',
        'description' => 'string',
        'performers' => 'array',
        'torrent' => 'string',
        'gallery' => 'array'
    ];
}
