<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Flickr\Jobs\FlickrContacts;
use App\Modules\Flickr\Models\FlickrContacts as FlickrContactModel;

class FlickrContactsTest extends TestCase
{
    public function testGetContacts()
    {
        FlickrContactModel::truncate();

        FlickrContacts::dispatch();

        $this->assertDatabaseCount('flickr_contacts', 1102, 'mongodb');
    }
}
