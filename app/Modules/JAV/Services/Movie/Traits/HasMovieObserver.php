<?php

namespace App\Modules\JAV\Services\Movie\Traits;

use App\Modules\JAV\Services\Movie\Observers\MovieObserver;

trait HasMovieObserver
{
    public static function bootHasMovieObserver()
    {
        static::observe(MovieObserver::class);
    }

    public function initializeHasMovie(): void
    {
        $this->mergeFillable([
            'dvd_id',
            'genres',
            'performers',
            'url',
            'gallery'
        ]);
        $this->mergeCasts([
            'dvd_id' => 'string',
            'genres' => 'array',
            'performers' => 'array',
            'url' => 'string',
            'gallery' => 'array'
        ]);
    }
}
