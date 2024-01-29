<?php

namespace App\Modules\Client\Tests\Unit\Repositories;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\StateMachine\Integration\InitState;
use App\Modules\Client\StateMachine\Integration\InProgressState;
use App\Modules\Client\Tests\TestCase;
use App\Modules\Core\God\Generator;
use App\Modules\Flickr\Services\FlickrService;

class IntegrationRepositoryTest extends TestCase
{
    public function testGetItems()
    {
        $integration = app(Generator::class)
            ->integration()
            ->factory()
            ->get();
        $integration->transitionTo(InProgressState::class);

        $integrations = app(IntegrationRepository::class)
            ->getItems($integration->service, null, InProgressState::class);
        $this->assertEquals(1, $integrations->count());

        $this->expectException(NoIntegrateException::class);
        app(IntegrationRepository::class)
            ->getItems($this->faker->word);
    }

    public function testGetInit()
    {
        $integration = Integration::factory()->create(['service' => 'test']);
        $integrations = app(IntegrationRepository::class)
            ->getInit($integration->service);

        $this->assertEquals(1, $integrations->count());
        $this->assertTrue($integrations->first()->isState(InitState::class));
    }

    public function testGetCompleted()
    {
        $integration =  Integration::factory()->create([
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => CompletedState::class
        ]);
        $integrations = app(IntegrationRepository::class)
            ->getCompleted($integration->service);

        $this->assertEquals(1, $integrations->count());

        $this->assertTrue($integrations->first()->isState(CompletedState::class));
    }
}
