<?php

namespace App\Modules\JAV\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'dvd_id',
        'url',
        'cover',
        'torrent',
        'size',
        'gallery'
    ];

    protected $casts = [
        'dvd_id' => 'string',
        'url' => 'string',
        'cover' => 'string',
        'torrent' => 'string',
        'size' => 'float',
        'gallery' => 'array'
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(MovieGenre::class, 'movie_genre');
    }

    public function performers(): BelongsToMany
    {
        return $this->belongsToMany(MoviePerformer::class, 'movie_performer');
    }
}
