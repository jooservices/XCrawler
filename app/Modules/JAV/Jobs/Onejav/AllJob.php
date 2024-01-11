<?php

namespace App\Modules\JAV\Jobs\Onejav;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Exceptions\OnejavRetryFailed;
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
     * @param OnejavService $service
     * @return void
     * @throws OnejavRetryFailed
     */
    public function handle(OnejavService $service)
    {
        $items = $service->all($this->endpoint);
        if (!$items->has('items')) {
            return;
        }

        $items->items->each(function ($item) use ($service) {
            $service->create($item->toArray());
        });
    }
}
