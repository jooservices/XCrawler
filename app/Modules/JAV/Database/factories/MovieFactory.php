<?php

namespace App\Modules\JAV\Database\factories;

use App\Modules\JAV\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Movie::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'dvd_id' => $this->faker->uuid,
            'url' => $this->faker->url,
            'cover' => $this->faker->imageUrl(),
            'torrent' => $this->faker->url,
            'size' => $this->faker->randomFloat(2, 0, 100),
            'gallery' => [
                $this->faker->imageUrl,
                $this->faker->imageUrl,
            ]
        ];
    }
}

