<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Exceptions\EntityCastException;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\Exceptions\UserDeletedException;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

class PeopleInfoJob extends BaseJob
{
    public function __construct(public string $nsid)
    {
    }

    /**
     * @param IntegrationRepository $repository
     * @return void
     * @throws EntityCastException
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws NoIntegrateException
     * @throws PermissionDeniedException
     */
    public function handle(IntegrationRepository $repository): void
    {
        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);
        /**
         * @var PeopleInfoEntity $info
         */
        $info = app(FlickrService::class)->setIntegration($integration)
            ->people->getInfo($this->nsid);

        app(FlickrContactService::class)->create($info->toArray());
    }

    /**
     * @throws UserDeletedException
     */
    public function failed(Throwable $throwable)
    {
        switch ($throwable->getCode()) {
            // User not found
            case 1:
            // User deleted
            case 5:
                $contact = FlickrContact::where('nsid', $this->nsid)->first();
                $contact->tasks()->delete();
                $contact->delete();
                throw new UserDeletedException(
                    $throwable->getMessage(),
                    $throwable->getCode(),
                    $throwable
                );
        }
    }
}
