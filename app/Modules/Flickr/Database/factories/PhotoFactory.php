<?php

namespace App\Modules\Flickr\Database\factories;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = FlickrPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'owner' => FlickrContact::factory()->create()->nsid,
            'farm' => $this->faker->numberBetween(),
            'isfamily' => $this->faker->boolean,
            'isfriend' => $this->faker->boolean,
            'ispublic' => $this->faker->boolean,
            'secret' => $this->faker->uuid,
            'server' => $this->faker->numberBetween(),
            'title' => $this->faker->sentence,
            'sizes' => $this->faker->randomElements(),
        ];
    }
}
