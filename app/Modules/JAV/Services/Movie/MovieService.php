<?php

namespace App\Modules\JAV\Services\Movie;

use App\Modules\JAV\Models\Movie;
use App\Modules\JAV\Models\MovieGenre;
use App\Modules\JAV\Models\MoviePerformer;
use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use Illuminate\Support\Str;

class MovieService
{
    private Movie $movie;

    public function create(MovieEntityInterface $movie): void
    {
        $this->movie = Movie::firstOrCreate([
            'dvd_id' => $movie->getDvdId(),
        ], [
            'url' => $movie->getUrl(),
        ]);

        $this->insertPerformers($movie);
        $this->insertGenres($movie);
    }

    private function insertPerformers(MovieEntityInterface $movie): void
    {
        $performers = $movie->getPerformers();
        $performers = array_diff(
            $performers,
            MoviePerformer::whereIn('name', $performers)->pluck('name')->toArray()
        );

        collect($performers)->each(function ($performer) {
            $performer = MoviePerformer::firstOrCreate([
                'name' => $performer
            ], [
                'uuid' => Str::orderedUuid(),
                'name' => $performer,
            ]);

            $this->movie->performers()->syncWithoutDetaching($performer);
        });
    }

    private function insertGenres(MovieEntityInterface $movie): void
    {
        $genres = $movie->getGenres();
        $genres = array_diff(
            $genres,
            MovieGenre::whereIn('name', $genres)->pluck('name')->toArray()
        );

        collect($genres)->each(function ($genre) {
            $genre = MovieGenre::firstOrCreate([
                'name' => $genre
            ], [
                'uuid' => Str::orderedUuid(),
                'name' => $genre,
            ]);

            $this->movie->genres()->syncWithoutDetaching($genre);
        });
    }
}
