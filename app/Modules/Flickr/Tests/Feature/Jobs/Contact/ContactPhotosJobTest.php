<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Exception;
use GuzzleHttp\Client;
use Mockery;
use Mockery\MockInterface;

class ContactPhotosJobTest extends TestCase
{
    public function testGetPeoplePhotos()
    {
        $contact = app(FlickrContactService::class)->create(['nsid' => '73115043@N07',]);
        $task = $contact->refresh()->tasks()
            ->where('task', TaskService::TASK_CONTACT_PHOTOS)->first();
        $task->state_code->transitionTo(InProgressState::class);

        ContactPhotosJob::dispatch($this->integration, $task);

        $this->assertDatabaseCount('flickr_photos', 507);
        $this->assertEquals(507, $contact->photos()->count());
        $this->assertEquals(CompletedState::class, $task->refresh()->state_code);
    }

    public function testWhenJobFailed()
    {
        $contact = app(FlickrContactService::class)->create(['nsid' => '73115043@N07',]);
        $task = $contact->refresh()->tasks()
            ->where('task', TaskService::TASK_CONTACT_PHOTOS)->first();

        $task->state_code->transitionTo(InProgressState::class);

        $this->instance(
            FlickrService::class,
            Mockery::mock(FlickrService::class, function (MockInterface $mock) {
                $mock->shouldReceive('setIntegration')
                    ->andThrow(new Exception('Test exception'));
            })
        );

        $this->expectException(Exception::class);
        ContactPhotosJob::dispatch($this->integration, $task);
        $this->assertEquals(FailedState::class, $task->refresh()->state_code);
    }
}
