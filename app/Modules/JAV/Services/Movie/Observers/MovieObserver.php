<?php

namespace App\Modules\JAV\Services\Movie\Observers;

use App\Modules\JAV\Models\MovieGenre;
use App\Modules\JAV\Models\MoviePerformer;
use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MovieObserver
{
    public function __construct()
    {
    }

    /**
     * Handle created event.
     *
     * @return void
     */
    public function created(MovieEntityInterface $model)
    {
        $this->insertGenres($model);
        $this->insertPerformers($model);
    }

    private function insertGenres(MovieEntityInterface $movie)
    {
        $genres = $movie->getGenres();
        $genres = array_diff(
            $genres,
            MovieGenre::whereIn('name', $genres)->pluck('name')->toArray()
        );

        $now = Carbon::now();
        MovieGenre::insert(
            collect($genres)->map(function ($genre) use ($now) {

                return [
                    'uuid' => Str::orderedUuid(),
                    'name' => $genre,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            })->toArray()
        );
    }

    private function insertPerformers(MovieEntityInterface $movie)
    {
        $performers = $movie->getPerformers();
        $performers = array_diff(
            $performers,
            MoviePerformer::whereIn('name', $performers)->pluck('name')->toArray()
        );

        $now = Carbon::now();
        MoviePerformer::insert(
            collect($performers)->map(function ($performer) use ($now) {

                return [
                    'uuid' => Str::orderedUuid(),
                    'name' => $performer,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            })->toArray()
        );
    }
}
