<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;
use Carbon\Carbon;

class OnejavCrawlingDaily extends BaseJob
{
    public function uniqueId(): string
    {
        return Carbon::now()->format('Y/m/d');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OnejavService $service)
    {
        $service->daily()
            ->each(function ($item) use ($service) {
                $service->create($item->getProperties());
            });
    }
}
