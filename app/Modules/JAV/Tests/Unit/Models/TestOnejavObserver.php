<?php

namespace App\Modules\JAV\Tests\Unit\Models;

use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Tests\TestCase;

class TestOnejavObserver extends TestCase
{
    public function testBootHasMovieObserver()
    {
        $onejav = Onejav::factory()->create();

        $this->assertTrue(in_array('dvd_id', $onejav->getFillable()));
        $this->assertTrue(in_array('genres', $onejav->getFillable()));
        $this->assertTrue(in_array('performers', $onejav->getFillable()));
        $this->assertTrue(in_array('url', $onejav->getFillable()));
        $this->assertTrue(in_array('gallery', $onejav->getFillable()));
    }
}
