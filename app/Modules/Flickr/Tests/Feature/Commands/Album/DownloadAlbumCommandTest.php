<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Album;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetReadyForDownload;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class DownloadAlbumCommandTest extends TestCase
{
    private const PHOTOSET_ID = '72157674594210788';

    public function setUp(): void
    {
        parent::setUp();

        $this->integration = Integration::factory()
            ->service(FlickrService::SERVICE_NAME)
            ->primary(false)
            ->create();
    }

    public function testNoPhotosetPhotosFetched()
    {
        Queue::fake(PhotosetPhotosJob::class);

        $this->artisan('flickr:download-album --photoset_id=' . self::PHOTOSET_ID);
        // One request to fetch photoset info
        $this->assertEquals(1, $this->integration->fresh()->requested_times);
        // Contact created for relationship
        $this->assertDatabaseHas('flickr_contacts', ['nsid' => '94529704@N02']);
        // Photoset created for relationship
        $this->assertDatabaseHas('flickr_photosets', [
            'id' => self::PHOTOSET_ID,
            'owner' => '94529704@N02',
            'title' => 'Phương Trần',
            'photos' => 1
        ]);
        $this->assertDatabaseHas('tasks', [
            'model_type' => FlickrPhotoset::class,
            'model_id' => self::PHOTOSET_ID,
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET,
            'state_code' => States::STATE_INIT
        ]);

        // Make sure payload is corrected
        $task = Task::where('model_id', 72157674594210788)
            ->where('model_type', FlickrPhotoset::class)
            ->first();
        $this->assertEquals(1, $task->payload['photos']);

        // Task to fetch photoset's photos
        $this->assertDatabaseHas('tasks', [
            'model_type' => FlickrPhotoset::class,
            'model_id' => self::PHOTOSET_ID,
            'task' => FlickrService::TASK_PHOTOSET_PHOTOS
        ]);

        Queue::assertPushed(PhotosetPhotosJob::class);
    }

    public function testWithPhotosetPhotosFetched()
    {
        Event::fake(PhotosetReadyForDownload::class);

        /**
         * Prepare init data
         */
        $contact = FlickrContact::factory()->create([
            'nsid' => '94529704@N02'
        ]);

        $photoset = FlickrPhotoset::factory()->create([
            'id' => '72157674594210788',
            'owner' => $contact->nsid
        ]);

        $photoset->relationshipPhotos()->create([
            'id' => $this->faker->numerify,
            'owner' => $photoset->owner
        ]);

        $this->assertFalse($photoset->tasks()->exists());

        $this->artisan('flickr:download-album --photoset_id=72157674594210788');
        // One request to fetch photoset info
        $this->assertEquals(1, $this->integration->fresh()->requested_times);
        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => '94529704@N02'
        ]);
        $this->assertDatabaseHas('flickr_photosets', [
            'id' => self::PHOTOSET_ID,
            'owner' => '94529704@N02',
            'title' => 'Phương Trần',
            'photos' => 1
        ]);

        $this->assertTrue(
            $photoset->tasks()
                ->where('task', FlickrService::TASK_DOWNLOAD_PHOTOSET)
                ->exists()
        );
        Event::assertDispatched(PhotosetReadyForDownload::class);
    }
}
