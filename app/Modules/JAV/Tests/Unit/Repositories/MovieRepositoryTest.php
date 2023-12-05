<?php

namespace App\Modules\JAV\Tests\Unit\Repositories;

use App\Modules\JAV\Models\Movie;
use App\Modules\JAV\Repositories\MovieRepository;
use App\Modules\JAV\Tests\TestCase;

class MovieRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MovieRepository::class);
    }

    public function testGetMovies()
    {
        Movie::factory()->count(10)->create();

        $this->assertEquals(10, $this->repository->items());
    }
}
