<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\JAV\Models\MovieGenre;
use App\Modules\JAV\Models\MoviePerformer;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Services\Movie\MovieService;
use App\Modules\JAV\Tests\TestCase;

class MovieServiceTest extends TestCase
{
    public function testCreateMovie()
    {
        Onejav::truncate();
        MoviePerformer::truncate();
        MovieGenre::truncate();

        $onejav = Onejav::create([
            'genres' => [
                'genre1',
                'genre2',
            ],
            'performers' => [
                'performer1',
                'performer2',
                'performer3',
            ]
        ]);

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
    }
}
