<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Console\PhotosSizesCommand;
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
            'status' => States::STATE_COMPLETED,
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
}