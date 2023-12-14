<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class ContactFavoritesCommandTest extends TestCase
{
    public function testHandle()
    {
        Queue::fake();

        /**
         * Service create Contact and also create tasks
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker->uuid]);
        $this->assertEquals(2, $contact->refresh()->tasks->count());
        $this->assertEquals(1, $contact->tasks()->where('task', FlickrService::TASK_CONTACT_FAVORITES)->count());

        $this->artisan(FavoritesCommand::COMMAND)->assertExitCode(0);
        Queue::assertPushed(ContactFavoritesJob::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });

        // Whenever task is fetched, it should be deleted.
        $this->assertCount(0, $contact->refresh()
            ->tasks()
            ->where('task', FlickrService::TASK_CONTACT_FAVORITES)->get());
    }
}
