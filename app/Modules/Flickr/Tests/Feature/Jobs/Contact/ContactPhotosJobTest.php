<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class ContactPhotosJobTest extends TestCase
{
    public function testGetPeoplePhotos()
    {
        $contact = app(FlickrContactService::class)->create(['nsid' => '73115043@N07',]);
        $task = $contact->refresh()->tasks()
            ->where('task', FlickrService::TASK_CONTACT_PHOTOS)->first();

        ContactPhotosJob::dispatch($this->integration, $task);

        $this->assertDatabaseCount('flickr_photos', 507);
        $this->assertEquals(507, $contact->photos()->count());
        $this->assertEquals(States::STATE_COMPLETED, $task->refresh()->state_code);
    }
}
