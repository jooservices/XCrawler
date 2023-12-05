<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\JAV\Models\Movie;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Services\Movie\MovieService;
use App\Modules\JAV\Tests\TestCase;

class MovieServiceTest extends TestCase
{
    private Onejav $onejav;

    public function setUp(): void
    {
        parent::setUp();

        $this->onejav = Onejav::factory()->create();
    }

    public function testCreateMovie()
    {

        $genres = $this->onejav->getGenres();
        $performers = $this->onejav->getPerformers();

        app(MovieService::class)->create($this->onejav);

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('genres', ['name' => $genre]);
        }

        foreach ($performers as $performer) {
            $this->assertDatabaseHas('performers', ['name' => $performer]);
        }

        $this->assertDatabaseHas('movies', [
            'dvd_id' => $this->onejav->getDvdId(),
            'url' => $this->onejav->getUrl(),
            'cover' => $this->onejav->getCover(),
        ]);

        $movie = Movie::all()->first();
        $this->assertEquals($performers, $movie->performers->pluck('name')->toArray());
        $this->assertEquals($genres, $movie->genres->pluck('name')->toArray());
    }

    public function testCreateMovieDuplicate()
    {
        $genres = $this->onejav->getGenres();
        $performers = $this->onejav->getPerformers();

        app(MovieService::class)->create($this->onejav);

        $this->assertDatabaseHas('movies', [
            'dvd_id' => $this->onejav->getDvdId(),
            'url' => $this->onejav->getUrl(),
            'cover' => $this->onejav->getCover(),
        ]);

        $movie = Movie::all()->first();
        $this->assertEquals($performers, $movie->performers->pluck('name')->toArray());
        $this->assertEquals($genres, $movie->genres->pluck('name')->toArray());

        Onejav::factory()->create([
            'dvd_id' => $this->onejav->getDvdId(),
        ]);
        app(MovieService::class)->create($this->onejav);

        $this->assertEquals(1, Movie::all()->count());
    }
}
