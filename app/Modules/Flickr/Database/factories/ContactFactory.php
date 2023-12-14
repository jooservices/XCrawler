<?php

namespace App\Modules\Flickr\Database\factories;

use App\Modules\Flickr\Models\FlickrContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = FlickrContact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'nsid' => $this->faker->uuid,
            'username' => $this->faker->userName,
            'realname' => $this->faker->name,
            'friend' => $this->faker->boolean,
            'family' => $this->faker->boolean,
            'ignored' => $this->faker->boolean,
            'rev_ignored' => $this->faker->boolean,
            'iconserver' => $this->faker->randomNumber(),
            'iconfarm' => $this->faker->randomNumber(),
            'path_alias' => $this->faker->userName,
            'location' => $this->faker->city,
        ];
    }
}
