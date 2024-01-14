<?php

namespace App\Modules\Flickr\Services;

class TaskService
{
    public const TASK_CONTACT_FAVORITES = 'contact-favorites';
    public const TASK_CONTACT_PHOTOS = 'contact-photos';
    public const TASK_CONTACT_PHOTOSETS = 'contact-photosets';
    public const TASK_CONTACT_INFO = 'contact-info';

    public const CONTACT_TASKS = [
        self::TASK_CONTACT_FAVORITES,
        self::TASK_CONTACT_PHOTOS,
        self::TASK_CONTACT_PHOTOSETS,
        self::TASK_CONTACT_INFO
    ];
}
