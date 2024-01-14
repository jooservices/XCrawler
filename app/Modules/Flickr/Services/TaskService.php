<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Core\Services\TaskService as CoreTaskService;

class TaskService extends CoreTaskService
{
    public const TASK_CONTACT_FAVORITES = 'contact-favorites';
    public const TASK_CONTACT_PHOTOS = 'contact-photos';
    public const TASK_CONTACT_PHOTOSETS = 'contact-photosets';
    public const TASK_CONTACT_INFO = 'contact-info';

    public const TASK_PHOTOSET_PHOTOS = 'photoset-photos';

    public const TASK_DOWNLOAD_PHOTOSET = 'download-photoset';
    public const TASK_DOWNLOAD_PHOTOSET_PHOTO = 'download-photoset-photo';
    public const TASK_UPLOAD_PHOTOSET = 'upload-photoset';
    public const TASK_UPLOAD_PHOTO = 'upload-photo';

    public const CONTACT_TASKS = [
        self::TASK_CONTACT_FAVORITES,
        self::TASK_CONTACT_PHOTOS,
        self::TASK_CONTACT_PHOTOSETS,
        self::TASK_CONTACT_INFO
    ];
}
