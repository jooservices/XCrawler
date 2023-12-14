<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlickrPhoto extends Model implements TaskInterface
{
    use HasUuid;
    use HasTasks;
    use HasFactory;

    /**
     * Mapping with Flickr photo.id
     */
    public $incrementing = false;

    protected $fillable = [
        'owner',
        'farm',
        'isfamily',
        'isfriend',
        'ispublic',
        'secret',
        'server',
        'title',
        'sizes',
        'dateuploaded',
        'views',
        'media',
    ];

    protected $casts = [
        'owner' => 'string',
        'farm' => 'integer',
        'isfamily' => 'boolean',
        'isfriend' => 'boolean',
        'ispublic' => 'boolean',
        'secret' => 'string',
        'server' => 'string',
        'title' => 'string',
        'sizes' => 'array',
        'dateuploaded' => 'datetime',
        'views' => 'integer',
        'media' => 'string',
    ];

    protected $table = 'flickr_photos';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FlickrContact::class, 'owner', 'nsid');
    }

    protected static function newFactory(): PhotoFactory
    {
        return PhotoFactory::new();
    }
}
