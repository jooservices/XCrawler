<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Flickr\Database\factories\PhotosetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $owner
 * @property int $photos
 * @property string $title
 */
class FlickrPhotoset extends Model implements TaskInterface
{
    use HasUuid;
    use HasFactory;
    use HasTasks;

    public const TASK_PHOTOSET_PHOTOS = 'photoset-photos';

    protected $table = 'flickr_photosets';

    public $incrementing = false;

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

    protected $casts = [
        'id' => 'int',
        'owner' => 'string',
        'primary' => 'string',
        'secret' => 'string',
        'server' => 'string',
        'farm' => 'int',
        'count_photos' => 'int',
        'count_videos' => 'int',
        'title' => 'string',
        'description' => 'string',
        'photos' => 'int',
    ];

    protected static function newFactory(): PhotosetFactory
    {
        return PhotosetFactory::new();
    }

    public function relationshipPhotos(): BelongsToMany
    {
        return $this->belongsToMany(FlickrPhoto::class, 'flickr_photosets_photos', 'photoset_id', 'photo_id')
            ->withTimestamps();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FlickrContact::class, 'owner', 'nsid');
    }

    public function googlePhotoAlbum(): HasOne
    {
        return $this->hasOne(GooglePhotoAlbum::class, 'flickr_photoset_id', 'id');
    }

    public function createGooglePhotoAlbum(): GooglePhotoAlbum
    {
        if ($this->googlePhotoAlbum) {
            return $this->googlePhotoAlbum;
        }

        $googlePhotoService = app(GooglePhotos::class);
        $albumId = $googlePhotoService->createAlbum($this->title);

        return $this->googlePhotoAlbum()->create([
            'title' => $this->title,
            'album_id' => $albumId
        ]);
    }
}
