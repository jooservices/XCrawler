<?php

namespace App\Modules\JAV\Tests\Unit\Entities;

use App\Modules\JAV\Entities\Onejav\MovieEntity;
use App\Modules\JAV\Tests\TestCase;
use Carbon\Carbon;

class OnejavEntityTest extends TestCase
{
    public function testToArray()
    {
        $entity = new MovieEntity([
            'url' => 'https://onejav.com/ABP-123',
            'date' => Carbon::now(),
            'genres' => '["genre1", "genre2"]',
        ]);

        $this->assertEquals('https://onejav.com/ABP-123', $entity->url);
        $this->assertNull($entity->dvd_id);
        $this->assertNull($entity->size);
        $this->assertNull($entity->cover);
        $this->assertNull($entity->torrent);
        $this->assertNull($entity->gallery);

        $this->assertIsArray($entity->genres);
    }
}
