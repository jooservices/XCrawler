<?php

namespace App\Modules\Client\Database\factories;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Services\FlickrService;
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
            'service' => FlickrService::SERVICE_NAME,
            'name' => 'flickr',
            'key' => $this->faker->uuid,
            'secret' => $this->faker->uuid,
            'callback' => $this->faker->url,
            'is_primary' => true,
            'token' => $this->faker->uuid,
            'token_secret' => $this->faker->uuid,
            'state_code' => States::STATE_COMPLETED,
        ];
    }

    public function service(string $service): Factory
    {
        return $this->state(function (array $attributes) use ($service) {
            return [
                'service' => $service,
            ];
        });
    }

    public function primary(bool $isPrimary = true): Factory
    {
        return $this->state(function (array $attributes) use ($isPrimary) {
            return [
                'is_primary' => $isPrimary,
            ];
        });
    }

    public function init(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'state_code' => States::STATE_INIT,
            ];
        });
    }
}
