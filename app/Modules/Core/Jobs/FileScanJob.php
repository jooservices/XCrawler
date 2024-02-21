<?php

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Models\File;
use App\Modules\Core\Services\FileScannerService;

class FileScanJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $filePath)
    {
    }


    public function handle(): void
    {
        $video = app(FileScannerService::class)->ffprobe($this->filePath);
        $fileInfo = pathinfo($this->filePath);

        File::updateOrCreate(
            [
                'storage' => 'local',
                'hash' => hash_file('sha256', $this->filePath),
                'name' => $fileInfo['basename'],
                'path' => $fileInfo['dirname'],
            ],
            [
                'type' => $video->get('codec_type'),
                'extension' => $fileInfo['extension'],
                'format' => $video->get('codec_tag_string'),
                'size' => filesize($this->filePath),
                'ratio' => $video->get('display_aspect_ratio'),
                'width' => $video->get('width'),
                'height' => $video->get('height'),
                'metadata' => $video->toArray(),
            ]
        );
    }
}
