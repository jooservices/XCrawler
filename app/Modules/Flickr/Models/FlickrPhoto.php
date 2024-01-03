<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlickrPhoto extends Model implements TaskInterface
{
    use HasUuid;
    use HasTasks;
    use HasFactory;
    use HasStates;

    /**
     * Mapping with Flickr photo.id
     */
    public $incrementing = false;

    protected $fillable = [
        'id',
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
        'id' => 'integer',
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

    public function photosets()
    {
    }

    protected static function newFactory(): PhotoFactory
    {
        return PhotoFactory::new();
    }

    public function createDownloadTask(): Task
    {
        return $this->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
            'state_code' => States::STATE_INIT
        ]);
    }
}
