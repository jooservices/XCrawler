<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Flickr\Console\PhotosSizesCommand;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Jobs\PhotosizesJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class PhotosSizesCommandTest extends TestCase
{
    public function testCommand()
    {
        Integration::factory()->create([
            'is_primary' => false,
            'state_code' => CompletedState::class
        ]);

        Queue::fake(PhotosizesJob::class);
        $photo = FlickrPhoto::factory()->create([
            'sizes' => null,
        ]);
        $this->artisan(PhotosSizesCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(PhotosizesJob::class, function (PhotosizesJob $job) use ($photo) {
            return $job->photo->id === $photo->id;
        });
    }

    public function testWhenPhotoNotFound()
    {
        Integration::factory()->create([
            'is_primary' => false,
            'state_code' => CompletedState::class
        ]);

        $photo = FlickrPhoto::factory()->create([
            'id' => 1,
            'sizes' => null,
        ]);
        $this->expectException(FailedException::class);
        $this->artisan(PhotosSizesCommand::COMMAND)->assertExitCode(0);

        $this->assertTrue($photo->refresh()->trashed());
    }
}
