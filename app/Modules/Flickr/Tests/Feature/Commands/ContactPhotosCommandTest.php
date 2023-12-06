<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class ContactPhotosCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake();

        $contact = FlickrContact::factory()->create();
        $task = $contact->tasks()->create([
            'task' => FlickrService::TASK_CONTACT_PHOTOS,
            'state_code' => States::STATE_INIT
        ]);

        $this->artisan(PhotosCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(ContactPhotosJob::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ], 'mongodb');

        $newTask = $contact->tasks()->where(
            'task',
            FlickrService::TASK_CONTACT_PHOTOS
        )->first();

        $this->assertFalse($newTask->is($task));
    }
}
