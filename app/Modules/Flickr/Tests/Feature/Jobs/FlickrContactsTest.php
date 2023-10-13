<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Tests\TestCase;
use App\Modules\Flickr\Jobs\FlickrContacts;

class FlickrContactsTest extends TestCase
{
    public function testGetContacts()
    {
        FlickrContacts::dispatch();

        $this->assertDatabaseCount('flickr_contacts', 1102, 'mongodb');
    }
}
