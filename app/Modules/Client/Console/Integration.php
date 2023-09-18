<?php

namespace App\Modules\Client\Console;

use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use Illuminate\Console\Command;

class Integration extends Command
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
    protected $description = 'Command description.';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $provider = app(ProviderFactory::class)->make(app(Flickr::class));
        $this->output->title('Integrate with Flickr');
        $requestToken = $provider->requestRequestToken();

        $this->output->text($provider->getAuthorizationUri(
            [
                'oauth_token' => $requestToken->getRequestToken(),
                'perms' => 'read'
            ]
        )->getAbsoluteUri());

        $code = $this->output->ask('Enter code');
        $accessToken = $provider->retrieveAccessToken($code);
        $this->output->table(
            ['Service', 'Token', 'Token Secret'],
            [
                ['Flickr', $accessToken->getAccessToken(), $accessToken->getAccessTokenSecret()],
            ]
        );

        return 0;
    }
}
