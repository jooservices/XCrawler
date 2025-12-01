<?php

namespace Modules\Flick\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Flick\Database\Factories\FlickPhotoFactory;

class FlickPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flick_photos';

    protected $fillable = [
        'flickr_id',
        'owner_nsid',
        'title',
        'secret',
        'server',
        'farm',
        'is_primary',
        'has_comment',
        'sizes_json',
        'is_downloaded',
        'local_path',
        'captured_at',
        'posted_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'has_comment' => 'boolean',
        'sizes_json' => 'array',
        'is_downloaded' => 'boolean',
        'captured_at' => 'datetime',
        'posted_at' => 'datetime',
        'farm' => 'integer',
    ];

    public function owner()
    {
        return $this->belongsTo(FlickContact::class, 'owner_nsid', 'nsid');
    }
}
