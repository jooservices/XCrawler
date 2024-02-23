<?php

namespace App\Modules\JAV\Services\Movie;

use App\Modules\JAV\Models\Movie;
use App\Modules\JAV\Models\MovieGenre;
use App\Modules\JAV\Models\MoviePerformer;
use App\Modules\JAV\Repositories\GenreRepository;
use App\Modules\JAV\Repositories\IdolRepository;
use App\Modules\JAV\Repositories\MovieRepository;
use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MovieService
{
    private Movie $movie;

    public function items(Collection $options): Collection
    {
        return app(MovieRepository::class)->items($options);
    }

    public function pagination(Collection $options): LengthAwarePaginator
    {
        return app(MovieRepository::class)->pagination($options);
    }

    public function genres(): Collection
    {
        return app(GenreRepository::class)->items();
    }

    public function idols(): Collection
    {
        return app(IdolRepository::class)->items();
    }

    /**
     * @param MovieEntityInterface $movie
     * @return Movie
     */
    public function create(MovieEntityInterface $movie): Movie
    {
        $this->movie = Movie::firstOrCreate([
            'dvd_id' => $movie->getDvdId(),
        ], [
            'url' => $movie->getUrl(),
            'cover' => $movie->getCover(),
        ]);

        $this->insertPerformers($movie);
        $this->insertGenres($movie);

        return $this->movie;
    }

    /**
     * @param MovieEntityInterface $movie
     * @return Movie
     */
    public function update(MovieEntityInterface $movie): Movie
    {
        $this->movie = Movie::updateOrCreate([
            'dvd_id' => $movie->getDvdId(),
        ], [
            'url' => $movie->getUrl(),
            'cover' => $movie->getCover(),
        ]);

        $this->insertPerformers($movie);
        $this->insertGenres($movie);

        return $this->movie;
    }

    private function insertPerformers(MovieEntityInterface $movie): void
    {
        $performers = $movie->getPerformers();
        if (empty($performers)) {
            return;
        }

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
        if (empty($genres)) {
            return;
        }

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
