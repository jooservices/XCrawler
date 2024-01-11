<?php

namespace App\Modules\Core\Events;

use Illuminate\Queue\SerializesModels;

class FileDownloaded
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public string $url, public string $saveTo)
    {
        //
    }
}
