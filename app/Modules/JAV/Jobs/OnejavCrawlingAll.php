<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Repositories\Onejav;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OnejavCrawlingAll implements ShouldQueue
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
    public function __construct(public string $endpoint = 'new')
    {
    }

    public function uniqueId(): string
    {
        return (string) Setting::getInt('onejav', $this->endpoint . '_current_page');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repository = app(Onejav::class);

        app(OnejavService::class)->{$this->endpoint}()
            ->each(function ($item) use ($repository) {
                $repository->firstOrCreate($item->getProperties());
            });
    }
}
