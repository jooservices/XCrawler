<?php

namespace App\Modules\Client\Tests\Unit\OAuth;

use App\Modules\Client\OAuth\Exceptions\RequestLimited;
use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class FlickrTest extends TestCase
{
    public function testRequestException()
    {
        $this->expectException(RequestLimited::class);

        $flickr = app(Flickr::class);
        Cache::set('flickr_requests_count', 3600);
        $flickr->request($this->faker->url);
    }
}
