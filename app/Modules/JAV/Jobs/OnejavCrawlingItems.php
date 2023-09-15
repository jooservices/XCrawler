<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\JAV\Repositories\Onejav;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OnejavCrawlingItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $url, private array $payload = [])
    {
    }

    public function uniqueId(): string
    {
        return md5(serialize([$this->url, $this->payload]));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repository = app(Onejav::class);
        app(OnejavService::class)->items($this->url, $this->payload)
            ->each(function ($item) use ($repository) {
                $repository->updateOrCreate($item->getProperties());
            });
    }
}
