<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class ContactPhotosCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake();

        /**
         * Service create Contact and also create tasks
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker->uuid]);
        $this->assertEquals(2, $contact->refresh()->tasks->count());
        $this->assertEquals(1, $contact->tasks()->where('task', FlickrService::TASK_CONTACT_PHOTOS)->count());

        $this->artisan(PhotosCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(ContactPhotosJob::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });

        // Whenever task is fetched, it should be deleted.
        $this->assertCount(0, $contact->refresh()
            ->tasks()
            ->where('task', FlickrService::TASK_CONTACT_PHOTOS)->get());
    }
}
