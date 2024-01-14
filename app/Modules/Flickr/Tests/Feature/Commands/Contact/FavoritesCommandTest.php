<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Contact;

use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class FavoritesCommandTest extends TestCase
{
    public function testHandle()
    {
        Queue::fake();

        /**
         * Service create Contact and also create tasks
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker->uuid]);
        $this->assertEquals(
            count(TaskService::CONTACT_TASKS),
            $contact->refresh()->tasks->count()
        );
        $this->assertEquals(1, $contact->tasks()->where('task', FlickrService::TASK_CONTACT_FAVORITES)->count());

        $this->artisan(FavoritesCommand::COMMAND)->assertExitCode(0);
        Queue::assertPushed(ContactFavoritesJob::class, function ($job) use ($contact) {
            return $job->task->model->is($contact);
        });
    }
}
