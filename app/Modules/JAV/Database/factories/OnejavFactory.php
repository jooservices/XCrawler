<?php

namespace App\Modules\JAV\Database\factories;

use App\Modules\JAV\Models\Onejav;
use Illuminate\Database\Eloquent\Factories\Factory;

class OnejavFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phpstan-ignore-next-line
     */
    protected $model = Onejav::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'url' => $this->faker->url,
            'dvd_id' => $this->faker->uuid,
            'genres' => [
                'genre1',
                'genre2',
            ],
            'performers' => [
                'performer1',
                'performer2',
                'performer3',
            ],
            'cover' => $this->faker->imageUrl(),
        ];
    }
}
