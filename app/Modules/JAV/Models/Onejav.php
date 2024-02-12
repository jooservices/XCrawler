<?php

namespace App\Modules\JAV\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\JAV\Database\factories\OnejavFactory;
use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use App\Modules\JAV\Services\Movie\Traits\HasMovieObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $url
 * @property string $dvd_id
 * @property array $genres
 * @property array $performers
 * @property string $torrent
 * @property array $gallery
 * @property string $cover
 *
 */
class Onejav extends Model implements MovieEntityInterface
{
    use HasMovieObserver;
    use HasFactory;
    use HasUuid;

    protected $table = 'onejav';

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
        return $this->dvd_id;
    }

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function getPerformers(): ?array
    {
        return $this->performers;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    protected static function newFactory(): OnejavFactory
    {
        return OnejavFactory::new();
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }
}
