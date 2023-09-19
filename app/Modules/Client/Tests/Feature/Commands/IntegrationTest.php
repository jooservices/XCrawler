<?php

namespace App\Modules\Client\Tests\Feature\Commands;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Tests\TestCase;

class IntegrationTest extends TestCase
{
    public function testFlickrIntegration()
    {
        Integration::truncate();

        $this->artisan('client:integration')
            ->expectsOutput('Integrate with Flickr')
            ->expectsQuestion('Enter code', '1234567890')
            ->assertExitCode(0);

        $this->assertDatabaseHas(
            'integrations',
            [
            'service' => 'flickr',
            'token_secret' =>  'test-secret',
            'token' => 'test-599ea1b1486d58e9'
            ],
            'mongodb'
        );
    }
}
