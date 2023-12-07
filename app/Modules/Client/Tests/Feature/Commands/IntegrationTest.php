<?php

namespace App\Modules\Client\Tests\Feature\Commands;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Tests\TestCase;
use App\Modules\Core\Services\States;

class IntegrationTest extends TestCase
{
    public function testFlickrIntegration()
    {
        $integration = Integration::factory()->create([
            'state_code' => States::STATE_INIT
        ]);

        $this->artisan('client:integration')
            ->expectsOutput('Integrate with Flickr')
            ->expectsQuestion('Enter code', '1234567890')
            ->assertExitCode(0);

        $this->assertEquals(States::STATE_COMPLETED, $integration->fresh()->state_code);
        $this->assertEquals('test-secret', $integration->fresh()->token_secret);
        $this->assertEquals('test-599ea1b1486d58e9', $integration->fresh()->token);
    }
}
