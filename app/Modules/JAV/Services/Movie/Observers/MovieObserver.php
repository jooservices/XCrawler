<?php

namespace App\Modules\JAV\Services\Movie\Observers;

use App\Modules\JAV\Services\Movie\Interfaces\MovieEntityInterface;
use App\Modules\JAV\Services\Movie\MovieService;

class MovieObserver
{
    /**
     * Handle created event.
     *
     * @return void
     */
    public function created(MovieEntityInterface $model)
    {
        app(MovieService::class)->create($model);
    }
}
