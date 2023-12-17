<?php

namespace App\Modules\Flickr\Tests\Unit\Models;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Tests\TestCase;

class PhotoTest extends TestCase
{
    public function testContactRelationship()
    {
        $photo = FlickrPhoto::factory()->create();
        $this->assertInstanceOf(FlickrPhoto::class, $photo);
        $this->assertInstanceOf(FlickrContact::class, $photo->contact);
        $this->assertCount(1, $photo->contact->photos);
    }
}
