<?php

namespace App\Modules\Client\Tests\Unit\Repositories;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Client\StateMachine\Integration\InitState;
use App\Modules\Client\StateMachine\Integration\InProgressState;
use App\Modules\Client\Tests\TestCase;

class IntegrationRepositoryTest extends TestCase
{
    public function testGetItems()
    {
        $integration = Integration::factory()->create([
            'service' => 'test'
        ]);
        $integration->state_code->transitionTo(InProgressState::class);

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
        $this->assertEquals(InitState::class, $integrations->first()->state_code->getValue());
    }

    public function testGetCompleted()
    {
        $integrations = app(IntegrationRepository::class)
            ->getCompleted($this->integration->service);

        $this->assertEquals(1, $integrations->count());

        $this->assertEquals(CompletedState::class, $integrations->first()->state_code->getValue());
    }
}
