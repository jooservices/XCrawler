<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Exceptions\HaveNoIntegration;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class InfoCommand extends Command implements Isolatable
{
    public const COMMAND = 'flickr:contact-info {--nsid=}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch contact\'s information';

    /**
     * @param IntegrationRepository $repository
     * @return void
     * @throws HaveNoIntegration
     */
    public function handle(IntegrationRepository $repository): void
    {
        $this->info('Fetching contact\' info ...');

        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);
        /**
         * @var PeopleInfoEntity $info
         */
        $info = app(FlickrService::class)->setIntegration($integration)
            ->people->getInfo($this->option('nsid'));

        $contact = FlickrContact::updateOrCreate([
            'nsid' => $info->nsid
        ], $info->toArray());

        $this->output->text('Updated contact ['. $info->nsid .']: ' . $contact->username);
    }
}