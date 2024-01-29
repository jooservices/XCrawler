<?php

namespace App\Modules\Flickr\Tests\God;

use App\Modules\Core\God\Generator;
use App\Modules\Core\Tests\TestCase;
use App\Modules\Flickr\Services\FlickrService;

class TestContacts extends TestCase
{
    public function testContacts()
    {
        $god = app(Generator::class);
        $god->mockClient('flickr', [
            ['contacts']
        ]);

        $contacts = app(FlickrService::class)
            ->contacts
            ->getList();

        dd($contacts);
    }
}
