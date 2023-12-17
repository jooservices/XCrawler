<?php

namespace App\Modules\Flickr\Tests\Unit\Models;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Tests\TestCase;

class PhotosetTest extends TestCase
{
    public function testRelationship()
    {
        $photoset = FlickrPhotoset::create([
            'id' => '123',
        ]);
        $contact = FlickrContact::factory()->create();

        $photoset->relationshipPhotos()->attach(FlickrPhoto::factory()->create());
        $photoset->contact()->associate($contact);

        $this->assertInstanceOf(FlickrPhoto::class, $photoset->relationshipPhotos->first());
        $this->assertInstanceOf(FlickrContact::class, $photoset->contact);
        $this->assertEquals($contact->nsid, $photoset->owner);
    }
}
