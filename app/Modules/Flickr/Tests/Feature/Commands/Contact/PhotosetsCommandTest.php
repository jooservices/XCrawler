<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Contact;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Console\Contact\PhotosetsCommand;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\TaskService;
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
            count(TaskService::CONTACT_TASKS),
            $contact->refresh()->tasks->count()
        );

        $this->assertEquals(1, $contact->tasks()->where('task', TaskService::TASK_CONTACT_PHOTOSETS)->count());

        $this->artisan(PhotosetsCommand::COMMAND)->assertExitCode(0);
        Queue::assertPushed(PhotosetsJob::class, function ($job) use ($contact) {
            return $job->task->model->is($contact)
                && $job->task->refresh()->state_code->getValue() === InProgressState::class;
        });
    }
}
