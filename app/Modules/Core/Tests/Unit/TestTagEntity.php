<?php

namespace App\Modules\Core\Tests\Unit;

use App\Modules\Core\Tests\TestCase;
use App\Modules\JAV\Entities\Onejav\TagEntity;

class TestTagEntity extends TestCase
{
    public function testCasting()
    {
        $tagEntity = new TagEntity([
            'url' => 'https://javtag.com/tag/3p/',
            'name' => '3P'
        ]);

        $this->assertIsString($tagEntity->url);
        $this->assertIsString($tagEntity->name);
    }
}
