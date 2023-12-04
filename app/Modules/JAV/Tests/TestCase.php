<?php

namespace App\Modules\JAV\Tests;

use App\Modules\JAV\Models\MovieGenre;
use App\Modules\JAV\Models\MoviePerformer;
use App\Modules\JAV\Models\Onejav;

class TestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Onejav::truncate();
    }
    public function getFixtures(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
