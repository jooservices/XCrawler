<?php

namespace App\Modules\Client\Tests\Feature\Commands;

use App\Modules\Client\Console\Integration\AddCommand;
use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\StateMachine\Integration\InitState;
use App\Modules\Core\God\Generator;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class IntegrationTest extends TestCase
{
    public function testFlickrIntegration()
    {
        $integration = app(Generator::class)
            ->integration()
            ->factory()
            ->get();

        $this->artisan('client:integration')
            ->expectsQuestion('Enter service: ', FlickrService::SERVICE_NAME)
            ->expectsQuestion('Choose integration: ', $integration->id)
            ->expectsQuestion('Enter code: ', 'test-599ea1b1486d58e9')
            ->assertExitCode(0);

        $this->assertTrue($integration->fresh()->isState(CompletedState::class));

        $this->assertEquals('test-secret', $integration->fresh()->token_secret);
        $this->assertEquals('test-599ea1b1486d58e9', $integration->fresh()->token);
    }

    public function testNoIntegration()
    {
        Integration::truncate();
        $this->expectException(NoIntegrateException::class);
        $this->artisan('client:integration')
            ->expectsQuestion('Enter service: ', FlickrService::SERVICE_NAME)
            ->assertExitCode(0);
    }

    public function testIntegrationAdd()
    {
        Integration::truncate();
        $this->assertDatabaseEmpty('integrations', 'mongodb');

        $this->artisan(AddCommand::COMMAND)
            ->expectsQuestion('Enter service name: ', 'test')
            ->expectsQuestion('Enter name: ', 'test')
            ->expectsQuestion('Enter key: ', 'test')
            ->expectsQuestion('Enter secret: ', 'test')
            ->expectsQuestion('Enter callback: ', 'test')
            ->expectsQuestion('Is primary? (y/n): ', 'y');

        $this->assertDatabaseHas(
            'integrations',
            [
                'service' => 'test',
                'name' => 'test',
                'key' => 'test',
                'secret' => 'test',
                'callback' => 'test',
                'is_primary' => true,
                'state_code' => InitState::class,
            ],
            'mongodb'
        );
    }
}
