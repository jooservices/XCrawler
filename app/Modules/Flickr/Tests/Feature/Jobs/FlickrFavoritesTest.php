<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Models\FlickrContacts;
use App\Modules\Flickr\Models\FlickrPhotos;

class FlickrFavoritesTest extends TestCase
{
    public function testGetPeopleFavorites()
    {
        FlickrContacts::truncate();
        FlickrPhotos::truncate();

        $nsid = '94529704@N02';
        $contact = FlickrContacts::create(['nsid' => $nsid,]);

        FlickrFavorites::dispatch($nsid);

        $this->assertDatabaseCount('flickr_photos', 1487, 'mongodb');
        $this->assertEquals(States::STATE_COMPLETED, $contact->fresh()->favorites_state_code);
    }
}
