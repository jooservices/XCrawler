<?php

namespace App\Modules\JAV\Jobs\Onejav;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;

class DailyJob extends BaseJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OnejavService $service)
    {
        $service->daily()
            ->items->each(function ($item) use ($service) {
                $service->create($item->toArray());
            });
    }
}
