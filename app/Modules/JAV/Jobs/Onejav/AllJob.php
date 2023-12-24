<?php

namespace App\Modules\JAV\Jobs\Onejav;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;

class AllJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $endpoint = 'new')
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OnejavService $service)
    {
        $service->all($this->endpoint)
            ->items->each(function ($item) use ($service) {
                $service->create($item->toArray());
            });
    }
}
