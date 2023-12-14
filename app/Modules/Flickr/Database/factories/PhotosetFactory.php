<?php

namespace App\Modules\Flickr\Database\factories;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhotoset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotosetFactory extends Factory
{
    protected $model = FlickrPhotoset::class;

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
        ];
    }
}
