<?php

namespace App\Modules\Flickr\Database\factories;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = FlickrPhoto::class;

    public const ID_WITH_SIZES = 51035586588;


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
            'sizes' => null
        ];
    }

    public function withSizes(): Factory
    {
        return $this->state(function () {
            return [
                'id' => self::ID_WITH_SIZES,
                'sizes' => [
                    [
                        'label' => 'Square',
                        'width' => 75,
                        'height' => 75,
                        'source' => 'https://live.staticflickr.com/65535/51035586588_1a6b0e2b9e_s.jpg',
                        'url' => 'https://www.flickr.com/photos/192911046@N06/51035586588/sizes/sq/',
                        'media' => 'photo'
                    ],
                ]
            ];
        });
    }
}
