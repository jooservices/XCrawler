<?php

namespace App\Modules\Client\Tests\Feature\Commands;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class IntegrationTest extends TestCase
{
    public function testFlickrIntegration()
    {
        $integration = Integration::factory()->create([
            'name' => 'test',
            'state_code' => States::STATE_INIT
        ]);

        $this->artisan('client:integration')
            ->expectsQuestion('Enter service: ', FlickrService::SERVICE_NAME)
            ->expectsQuestion('Choose integration: ', $integration->id)
            ->expectsQuestion('Enter code: ', 'test-599ea1b1486d58e9')
            ->assertExitCode(0);

        $this->assertEquals(States::STATE_COMPLETED, $integration->fresh()->state_code);
        $this->assertEquals('test-secret', $integration->fresh()->token_secret);
        $this->assertEquals('test-599ea1b1486d58e9', $integration->fresh()->token);
    }
}
