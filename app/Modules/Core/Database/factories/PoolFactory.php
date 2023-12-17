<?php

namespace App\Modules\Core\Database\factories;

use App\Modules\Core\Models\Pool;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\ContactsJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoolFactory extends Factory
{
    protected $model = Pool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'job' => ContactsJob::class,
            'queue' => 'flickr',
            'payload' => [],
            'state_code' => States::STATE_INIT
        ];
    }
}
