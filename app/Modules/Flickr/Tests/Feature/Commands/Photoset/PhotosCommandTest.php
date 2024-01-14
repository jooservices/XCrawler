<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Photoset;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Console\Photoset\PhotosCommand;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class PhotosCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake();

        $photoset = FlickrPhotoset::factory()->create();
        Event::dispatch(new PhotosetCreatedEvent($photoset));

        $this->assertEquals(
            1,
            $photoset->refresh()->tasks->count()
        );

        $this->assertEquals(1, $photoset->tasks()->where('task', FlickrPhotoset::TASK_PHOTOSET_PHOTOS)->count());

        $this->artisan(PhotosCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(PhotosetPhotosJob::class, function ($job) use ($photoset) {
            return $job->task->model->is($photoset)
                && $job->task->refresh()->state_code->getValue() === InProgressState::class;
        });
    }
}
