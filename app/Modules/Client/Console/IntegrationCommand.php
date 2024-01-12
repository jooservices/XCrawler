<?php

namespace App\Modules\Client\Console;

use App\Modules\Client\OAuth\OAuth1\IntegrationService;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Flickr\Services\FlickrService;
use Exception;
use Google\Client;
use Illuminate\Console\Command;

class IntegrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'client:integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integration.';

    /**
     * @throws Exception
     */
    public function handle(IntegrationRepository $repository): int
    {
        $service = $this->output->ask('Enter service: ');

        $integrations = $repository->getInit($service);
        if ($integrations->isEmpty()) {
            $this->output->error('No integrations found for ' . $service);
            return 0;
        }

        $integrations->each(function ($integration) {
            $this->output->table(
                ['Service', 'Name', 'ID'],
                [
                    [
                        ucfirst($integration->service),
                        $integration->name,
                        $integration->id,
                    ],
                ]
            );
        });

        $id = $this->output->ask('Choose integration: ', $integrations->first()->id);
        $integration = $integrations->where('id', $id)->first();

        if ($service === FlickrService::SERVICE_NAME) {
            $provider = app(ProviderFactory::class)->oauth1(
                app(Flickr::class),
                $integration
            );

            $integrateService = app(
                IntegrationService::class,
                [
                    'provider' => $provider,
                    'integration' => $integration
                ]
            );

            $this->output->title('Integrate with ' . ucfirst($service) . ' with ' . $integration->name);

            $this->output->text($integrateService->getAuthorizationUri());

            $accessToken = $integrateService->retrieveAccessToken($this->output->ask('Enter code: '));

            $this->output->table(
                ['Service', 'Name', 'ID', 'Token', 'Token Secret'],
                [
                    [
                        ucfirst($service),
                        $integration->name,
                        $integration->id,
                        $accessToken->getAccessToken(),
                        $accessToken->getAccessTokenSecret()
                    ],
                ]
            );
        } elseif ($service === GooglePhotos::SERVICE_NAME) {
            $client = new Client();
            $client->setClientId($integration->key);
            $client->setClientSecret($integration->secret);
            $client->addScope(GooglePhotos::GOOGLE_PHOTOS_SCOPES);
            $client->setRedirectUri(route('client.oauth.google'));
            $client->setAccessType('offline');
            $client->setIncludeGrantedScopes(true);
            $client->setPrompt('consent');
            $auth_url = $client->createAuthUrl();
            $this->output->info($auth_url);
        }

        return 0;
    }
}
