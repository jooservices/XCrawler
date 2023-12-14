<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlickrPhotoset extends Model implements TaskInterface
{
    use HasUuid;
    use HasFactory;
    use HasTasks;

    protected $table = 'flickr_photosets';

    protected $fillable = [
        'id',
        'owner',
        'primary',
        'secret',
        'server',
        'farm',
        'count_photos',
        'count_videos',
        'title',
        'description',
        'photos',
    ];

    public function relationshipPhotos(): BelongsToMany
    {
        return $this->belongsToMany(FlickrPhoto::class, 'flickr_photosets_photos', 'photoset_id', 'photo_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FlickrContact::class, 'owner', 'nsid');
    }
}
