<?php

namespace App\Modules\Client\Tests\Unit\OAuth;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;
use Tests\TestCase;

class ProviderFactoryTest extends TestCase
{
    public function test()
    {
        Integration::truncate();

        $integration = Integration::factory()->create();
        $provider = app(ProviderFactory::class)->make(app(Flickr::class));

        $this->assertInstanceOf(Flickr::class, $provider);
        $accessToken = $provider->getStorage()->retrieveAccessToken($provider->service());
        $this->assertEquals($integration->token, $accessToken->getAccessToken());
        $this->assertEquals($integration->token_secret, $accessToken->getAccessTokenSecret());
    }
}
