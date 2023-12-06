<?php

namespace App\Modules\Flickr\Tests;

use App\Modules\Client\Tests\TestCase as BaseTestCase;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FlickrContact::truncate();
        FlickrPhoto::truncate();
    }
}
