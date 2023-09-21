<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Repositories\Onejav;
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
    public function handle()
    {
        $repository = app(Onejav::class);
        app(OnejavService::class)->daily()
            ->each(function ($item) use ($repository) {
                $repository->create($item->getProperties());
            });
    }
}
