<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Tests\TestCase;

class ContactFavoritesJobTest extends TestCase
{
    public function testGetPeopleFavorites()
    {
        $nsid = '94529704@N02';

        ContactFavoritesJob::dispatch($nsid);

        $this->assertDatabaseCount('flickr_photos', 1487, 'mongodb');

        $photo = FlickrPhoto::where('owner', '<>', $nsid)->first();
        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => $photo->contact->nsid
        ], 'mongodb');

        $this->assertCount(2, $photo->contact->tasks);
    }
}
