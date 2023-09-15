<?php

namespace App\Modules\JAV\Models;

use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use App\Modules\JAV\Services\Movie\Traits\HasMovieObserver;
use Jenssegers\Mongodb\Eloquent\Model;

/**
* @property string $url
 * @property string $dvd_id
 * @property array $genres
 * @property array $performers
 * @property string torrent
 * @property array $gallery
 *
 */
class Onejav extends Model implements MovieEntityInterface
{
    use HasMovieObserver;

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
        'date' => 'date',
        'genres' => 'array',
        'description' => 'string',
        'performers' => 'array',
        'torrent' => 'string',
        'gallery' => 'array'
    ];

    public function getDvdId(): string
    {
        return (string)$this->dvd_id;
    }

    public function getGenres(): array
    {
        return (array)$this->genres;
    }

    public function getPerformers(): array
    {
        return (array)$this->performers;
    }

    public function getUrl(): string
    {
        return (string)$this->url;
    }

    public function getGallery(): array
    {
        return (array)$this->gallery;
    }
}
