<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Models\TaskInterface;
use App\Modules\Core\Models\Traits\HasTasks;
use App\Modules\Core\Models\Traits\HasUuid;
use App\Modules\Core\Services\FileManager;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property array $sizes
 */
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

    public function photosets(): BelongsToMany
    {
        return $this->belongsToMany(FlickrPhotoset::class, 'flickr_photosets_photos', 'photo_id', 'photoset_id');
    }

    protected static function newFactory(): PhotoFactory
    {
        return PhotoFactory::new();
    }

    public function createDownloadTask(): Task
    {
        return $this->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
        ]);
    }

    public function getSizes(): array
    {
        if ($this->sizes) {
            return $this->sizes;
        }

        $integration = app(IntegrationRepository::class)
            ->getNonPrimary(FlickrService::SERVICE_NAME);

        $sizes = app(FlickrService::class)
            ->setIntegration($integration)
            ->photos->getSizes($this->id);
        $this->update(['sizes' => $sizes,]);

        return $sizes;
    }

    public function getSize()
    {
        $sizes = $this->getSizes();
        return $sizes[count($sizes) - 1];
    }

    public function getOriginalSizeUrl()
    {
        return $this->getSize()['source'];
    }

    public function getOriginalSizeFile(): string
    {
        return explode(
            '?',
            pathinfo(
                $this->getOriginalSizeUrl(),
                PATHINFO_BASENAME
            )
        )[0];
    }

    public function uploadToGooglePhotos(string $googleAlbumId)
    {
        $storage = app(Filesystem::class);
        $filePath = $storage->path(FileManager::DOWNLOAD_PATH . '/' . $this->getOriginalSizeFile());

        $googlePhotoService = app(GooglePhotos::class);
        $googlePhotoService->createPhoto(
            $filePath,
            $this->getOriginalSizeFile(),
            $googleAlbumId
        );
    }
}
