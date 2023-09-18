<?php

namespace App\Modules\Client\Database\factories;

use App\Modules\Client\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
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
            'token' => $this->faker->text,
            'token_secret' => $this->faker->text,
            'data' => $this->faker->shuffleArray
        ];
    }
}
