<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Contact;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class PhotosCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake();

        /**
         * Service create Contact and also create tasks
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker->uuid]);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks->count());
        $this->assertEquals(1, $contact->tasks()->where('task', FlickrService::TASK_CONTACT_PHOTOS)->count());

        $this->artisan(PhotosCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(ContactPhotosJob::class, function ($job) use ($contact) {
            return $job->task->model->is($contact)
                && $job->task->refresh()->state_code->getValue() === InProgressState::class;
        });
    }
}
