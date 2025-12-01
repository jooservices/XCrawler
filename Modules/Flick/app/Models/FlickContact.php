<?php

namespace Modules\Flick\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Flick\Database\Factories\FlickContactFactory;

class FlickContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flick_contacts';

    protected $fillable = [
        'nsid',
        'username',
        'realname',
        'location',
        'iconserver',
        'iconfarm',
        'photos_count',
        'contacts_count',
        'crawl_status',
        'last_crawled_at',
        'is_monitored',
        'profile_url',
    ];

    protected $casts = [
        'last_crawled_at' => 'datetime',
        'photos_count' => 'integer',
        'contacts_count' => 'integer',
        'iconfarm' => 'integer',
        'is_monitored' => 'boolean',
    ];

    public function photos()
    {
        return $this->hasMany(FlickPhoto::class, 'owner_nsid', 'nsid');
    }
}
