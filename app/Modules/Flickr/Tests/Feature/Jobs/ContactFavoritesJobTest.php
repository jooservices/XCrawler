<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Tests\TestCase;

class ContactFavoritesJobTest extends TestCase
{
    public function testGetPeopleFavorites()
    {
        $nsid = '94529704@N02';

        $this->assertEquals(0, (int)$this->integration->refresh()->requested_times);
        ContactFavoritesJob::dispatch($this->integration, $nsid);

        $this->assertDatabaseCount('flickr_photos', 1487, 'mongodb');

        $photo = FlickrPhoto::where('owner', '<>', $nsid)->first();
        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => $photo->contact->nsid
        ], 'mongodb');

        $this->assertCount(2, $photo->contact->tasks);
        $this->assertEquals(4, $this->integration->refresh()->requested_times);
    }
}
