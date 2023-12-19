<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\SerializesModels;

class ContactPhotosJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrService $flickrService)
    {
        $flickrService->setIntegration($this->integration);
        $contactService = app(FlickrContactService::class);

        $photos = $flickrService->people->getPhotos([
            'user_id' => $this->task->model->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($photos->getItems());

        if ($photos->isCompleted()) {
            $this->task->updateState(States::STATE_COMPLETED);
            return;
        }

        self::dispatch($this->integration, $this->task, $photos->getNextPage());
    }
}
