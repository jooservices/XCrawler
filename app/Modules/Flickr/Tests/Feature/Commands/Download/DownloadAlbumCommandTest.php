<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Download;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;

class DownloadAlbumCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->integration = Integration::factory()
            ->service(FlickrService::SERVICE_NAME)
            ->primary(false)
            ->create([
                'state_code' => CompletedState::class,
            ]);

        $this->instance(
            GooglePhotos::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('createAlbum')
                    ->andReturn('test');
            })
        );

        Queue::fake(PeopleInfoJob::class);
    }

    public function testNoPhotosetPhotosFetched()
    {
        Queue::fake([
            PeopleInfoJob::class,
            PhotosetPhotosJob::class
        ]);

        $this->artisan('flickr:download-album --photoset_id=' . self::PHOTOSET_ID)
            ->expectsOutput('Download photoset: ' . self::PHOTOSET_ID . ' ...')
            ->expectsOutput('Getting contact: ' . self::NSID)
            ->expectsOutput('Dispatched people info job')
            ->expectsOutput('Getting photoset: ' . self::PHOTOSET_ID)
            ->expectsOutput('Photoset has 1 photos')
            ->expectsOutput('Preparing task')
            ->expectsOutput('Preparing photos')
            ->expectsOutput('Registered task ' . TaskService::TASK_PHOTOSET_PHOTOS)
            ->expectsOutput('There are no photos yet. Registered task to fetch photos of photoset')
            ->assertExitCode(0);

        Queue::assertPushed(PeopleInfoJob::class);

        /**
         * Prepare photoset
         * - One request to fetch photoset info
         */
        $this->assertEquals(1, $this->integration->fresh()->requested_times);

        /**
         * Prepare contact
         */
        $this->assertDatabaseHas('flickr_contacts', ['nsid' => self::NSID]);
        $this->assertDatabaseHas('flickr_photosets', [
            'id' => self::PHOTOSET_ID,
            'owner' => self::NSID,
            'title' => 'Phương Trần',
            'photos' => 1
        ]);

        /**
         * Prepare tasks
         */
        $this->assertDatabaseHas('tasks', [
            'model_type' => FlickrPhotoset::class,
            'model_id' => self::PHOTOSET_ID,
            'task' => TaskService::TASK_DOWNLOAD_PHOTOSET,
        ]);

        $task = Task::where('model_id', self::PHOTOSET_ID)
            ->where('model_type', FlickrPhotoset::class)
            ->where('task', TaskService::TASK_DOWNLOAD_PHOTOSET)
            ->first();

        $this->assertEquals(1, $task->payload['photos']);

        /**
         * Prepare photos
         */
        $this->assertTrue($task->isState(InProgressState::class));
        $this->assertEquals(1, $task->subTasks()->count());
        $this->assertEquals(TaskService::TASK_PHOTOSET_PHOTOS, $task->subTasks()->first()->task);

        Queue::assertPushed(PhotosetPhotosJob::class);
    }

    public function testWithPhotosetPhotosFetched()
    {
        Event::fake(PhotosetReadyForDownloadEvent::class);
        Queue::fake([PeopleInfoJob::class,]);

        /**
         * Prepare init data
         */
        $contact = FlickrContact::factory()->create(['nsid' => self::NSID]);

        $photoset = FlickrPhotoset::factory()->create([
            'id' => self::PHOTOSET_ID,
            'owner' => $contact->nsid
        ]);

        $photoset->relationshipPhotos()->create([
            'id' => $this->faker->numerify,
            'owner' => $photoset->owner
        ]);

        $this->assertFalse($photoset->tasks()->exists());

        $this->artisan('flickr:download-album --photoset_id=' . self::PHOTOSET_ID)
            ->assertExitCode(0);

        // One request to fetch photoset info
        $this->assertEquals(1, $this->integration->fresh()->requested_times);

        $this->assertDatabaseHas('flickr_contacts', ['nsid' => self::NSID]);
        $this->assertDatabaseHas('flickr_photosets', [
            'id' => self::PHOTOSET_ID,
            'owner' => self::NSID,
            'title' => 'Phương Trần',
            'photos' => 1
        ]);

        $this->assertTrue(
            $photoset->tasks()
                ->where('task', TaskService::TASK_DOWNLOAD_PHOTOSET)
                ->exists()
        );

        Event::assertDispatched(PhotosetReadyForDownloadEvent::class);
    }
}
