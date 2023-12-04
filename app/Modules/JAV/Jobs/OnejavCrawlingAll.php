<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class OnejavCrawlingAll extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $endpoint = 'new')
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->endpoint))->dontRelease()
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OnejavService $service)
    {
        $service->all($this->endpoint)
            ->each(function ($item) use ($service) {
                $service->create($item->getProperties());
            });
    }
}
