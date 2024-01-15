<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Exceptions\Google\GoogleAlbumNotFound;
use App\Modules\Flickr\Jobs\PhotoUploadJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;

class PhotoUploadJobTest extends TestCase
{
    public function testUploadWhenNoAlbumExists()
    {
        $photoset = FlickrPhotoset::factory()->create();
        $photo = FlickrPhoto::factory()->create();
        $photoset->relationshipPhotos()->syncWithoutDetaching($photo->id);
        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_UPLOAD_PHOTOSET,
            'payload' => [
                'photos' => 1
            ]
        ]);
        $subTask = $task->subTasks()->create([
            'model_id' => $photo->id,
            'model_type' => FlickrPhoto::class,
            'task' => TaskService::TASK_UPLOAD_PHOTO,
        ]);

        $this->expectException(GoogleAlbumNotFound::class);
        PhotoUploadJob::dispatch($subTask);
    }
}
