<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class OnejavCrawlingItems extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $url, private array $payload = [])
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(md5(serialize([$this->url, $this->payload]))))->dontRelease()
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OnejavService $service)
    {
        app(OnejavService::class)->items($this->url, $this->payload)
            ->items->each(function ($item) use ($service) {
                $service->create($item->toArray());
            });
    }
}
