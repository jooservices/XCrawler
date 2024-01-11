<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;

class PeopleInfoJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public string $nsid)
    {
    }

    public function handle(IntegrationRepository $repository)
    {
        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);
        /**
         * @var PeopleInfoEntity $info
         */
        $info = app(FlickrService::class)->setIntegration($integration)
            ->people->getInfo($this->nsid);

        app(FlickrContactService::class)->create($info->toArray());
    }
}
