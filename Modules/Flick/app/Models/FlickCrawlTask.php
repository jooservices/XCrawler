<?php

namespace Modules\Flick\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Flick\Database\Factories\FlickCrawlTaskFactory;

class FlickCrawlTask extends Model
{
    use HasFactory;

    protected $table = 'flick_crawl_tasks';

    protected $fillable = [
        'contact_nsid',
        'type',
        'page',
        'status',
        'hub_request_id',
        'priority',
        'depth',
        'payload',
        'retry_count',
        'max_retries',
        'last_error',
        'failed_at',
    ];

    protected $casts = [
        'page' => 'integer',
        'priority' => 'integer',
        'depth' => 'integer',
        'payload' => 'array',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'failed_at' => 'timestamp',
    ];

    public function contact()
    {
        return $this->belongsTo(FlickContact::class, 'contact_nsid', 'nsid');
    }
}
