<?php

namespace App\Modules\Core\Database\factories;

use App\Modules\Core\Models\Task;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task' => $this->faker->name,
            'model_type' => FlickrPhoto::class,
            'model_id' => FlickrPhoto::factory()->create()->id,
        ];
    }
}
