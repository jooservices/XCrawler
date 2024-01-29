<?php

namespace App\Modules\Flickr\Tests;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\Tests\TestCase as BaseTestCase;
use App\Modules\Core\God\Exceptions\NoProviderException;
use App\Modules\Core\God\Generator;
use App\Modules\Flickr\Services\FlickrService;

class TestCase extends BaseTestCase
{
    protected FlickrService $flickrService;
    protected Integration $integration;

    /**
     * @throws NoProviderException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->integration = Integration::factory()->create([
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => CompletedState::class
        ]);
        $this->flickrService = app(FlickrService::class)->setIntegration($this->integration);

        $god = app(Generator::class);
        $god->mockClient('flickr', [
            [
                'contacts',
                'favorites',
                'photos',
                'photosets',
                'people',
                'oauth',
            ]
        ]);
    }
}
