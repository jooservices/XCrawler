<?php

namespace App\Modules\JAV\Jobs\Onejav;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Services\OnejavService;

class ItemJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Onejav $item)
    {
    }

    /**
     * @param OnejavService $service
     * @return void
     */
    public function handle(OnejavService $service): void
    {
        $items = $service->items($this->item->url);
        $service->create(
            $items->items->first()->toArray()
        );
    }
}
