<?php

namespace App\Modules\Client\Database\factories;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpstan-ignore-next-line
     */
    protected $model = Integration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'service' => 'flickr',
            'key' => $this->faker->uuid,
            'secret' => $this->faker->uuid,
            'callback' => $this->faker->url,
            'is_primary' => true,
            'token' => $this->faker->uuid,
            'token_secret' => $this->faker->uuid,
            'data' => [],
            'state_code' => States::STATE_COMPLETED,
        ];
    }
}
