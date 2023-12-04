<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\JAV\Models\Movie;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Services\Movie\MovieService;
use App\Modules\JAV\Tests\TestCase;

class MovieServiceTest extends TestCase
{
    public function testCreateMovie()
    {
        $onejav = Onejav::factory()->create();

        app(MovieService::class)->create($onejav);
        $this->assertDatabaseHas('genres', [
            'name' => 'genre1'
        ]);
        $this->assertDatabaseHas('genres', [
            'name' => 'genre2'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer1'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer2'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer3'
        ]);

        $this->assertDatabaseHas('movies', [
            'dvd_id' => $onejav->getDvdId(),
            'url' => $onejav->getUrl(),
        ]);

        $movie = Movie::all()->first();
        $this->assertEquals([
            'performer1',
            'performer2',
            'performer3',
        ], $movie->performers->pluck('name')->toArray());
        $this->assertEquals([
            'genre1',
            'genre2',
        ], $movie->genres->pluck('name')->toArray());
    }

    public function testCreateMovieDuplicate()
    {
        $onejav = Onejav::factory()->create();
        app(MovieService::class)->create($onejav);
        $this->assertDatabaseHas('genres', [
            'name' => 'genre1'
        ]);
        $this->assertDatabaseHas('genres', [
            'name' => 'genre2'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer1'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer2'
        ]);
        $this->assertDatabaseHas('performers', [
            'name' => 'performer3'
        ]);

        $this->assertDatabaseHas('movies', [
            'dvd_id' => $onejav->getDvdId(),
            'url' => $onejav->getUrl(),
        ]);

        $movie = Movie::all()->first();
        $this->assertEquals([
            'performer1',
            'performer2',
            'performer3',
        ], $movie->performers->pluck('name')->toArray());
        $this->assertEquals([
            'genre1',
            'genre2',
        ], $movie->genres->pluck('name')->toArray());

        Onejav::factory()->create([
            'dvd_id' => $onejav->getDvdId(),
        ]);
        app(MovieService::class)->create($onejav);

        $this->assertEquals(1, Movie::all()->count());
    }
}
