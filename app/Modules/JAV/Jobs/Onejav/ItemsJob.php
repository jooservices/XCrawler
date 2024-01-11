<?php

namespace App\Modules\JAV\Jobs\Onejav;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ItemsJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(readonly string $url, private readonly array $payload = [])
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
