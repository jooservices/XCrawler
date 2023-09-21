<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Repositories\Onejav;
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
    public function handle()
    {
        $repository = app(Onejav::class);
        $endpoint = $this->endpoint ?? 'new';

        app(OnejavService::class)->all($endpoint)
            ->each(function ($item) use ($repository) {
                $repository->create($item->getProperties());
            });
    }
}
