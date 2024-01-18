<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Exceptions\EntityCastException;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\UserDeletedException;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;

class PeopleInfoJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public string $nsid)
    {
    }

    /**
     * @throws MissingEntityElement
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws NoIntegrateException
     * @throws GuzzleException
     * @throws EntityCastException
     */
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

    /**
     * @throws UserDeletedException
     */
    public function failed(\Throwable $exception)
    {
        // User deleted
        if ($exception->getCode() === 5) {
            $contact = FlickrContact::where('nsid', $this->nsid)->first();
            $contact->tasks()->delete();
            $contact->delete();
            throw new UserDeletedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
}
