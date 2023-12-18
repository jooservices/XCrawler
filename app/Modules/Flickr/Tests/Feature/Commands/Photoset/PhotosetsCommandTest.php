<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Photoset;

use App\Modules\Flickr\Console\Contact\PhotosetsCommand;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class PhotosetsCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake();

        /**
         * Service create Contact and also create tasks
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => '99097633@N00']);
        $this->assertEquals(
            count(FlickrService::CONTACT_TASKS),
            $contact->refresh()->tasks->count()
        );

        $this->assertEquals(1, $contact->tasks()->where('task', FlickrService::TASK_CONTACT_PHOTOSETS)->count());

        $this->artisan(PhotosetsCommand::COMMAND)->assertExitCode(0);
        Queue::assertPushed(PhotosetsJob::class, function ($job) use ($contact) {
            return $job->task->model->is($contact);
        });
    }
}
