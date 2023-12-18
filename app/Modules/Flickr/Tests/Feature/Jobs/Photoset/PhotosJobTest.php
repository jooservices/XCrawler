<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PhotosJobTest extends TestCase
{
    public function testGetPhotos()
    {
        $contact = FlickrContact::factory()->create([
            'nsid' => '94529704@N02',
        ]);

        $photoset = $contact->photosets()->create([
            'id' => '72157674594210788',
        ]);

        $task = $photoset->tasks()->create([
            'task' => FlickrService::TASK_PHOTOSET_PHOTOS,
        ]);

        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertEquals(1, $photoset->refresh()->relationshipPhotos->count());
    }
}
