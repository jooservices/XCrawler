<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Entities\VideoCodecEntity;
use App\Modules\Core\Jobs\FileScanJob;
use Illuminate\Support\Facades\File;
use RuntimeException;
use App\Modules\Core\Models\File as FileModel;

class FileScannerService
{
    public const ALLOWED_EXTENSIONS = [
        'mp4',
        'mkv',
        'avi',
        'wmv',
        'flv',
        'mov',
        'mpg',
        'mpeg',
    ];

    public const IGNORED_DIRECTORIES = [
        'lost+found',
        '.DS_Store',
        '.Trash-1000',
    ];

    public function scan(
        string $path,
        array $allowedExtensions = self::ALLOWED_EXTENSIONS,
        array $ignoredDirectories = self::IGNORED_DIRECTORIES
    ): void {
        $files = File::files($path);
        foreach ($files as $file) {
            if ($file->isDir()) {
                if (in_array($file->getFilename(), $ignoredDirectories, true)) {
                    continue;
                }

                $this->scan($file->getPathname());
                return;
            }

            if (in_array(strtolower($file->getExtension()), $allowedExtensions, true)) {
                FileScanJob::dispatch($file->getPathname())->onQueue('primary');
            }
        }
    }

    public function ffprobe(string $path): VideoCodecEntity
    {
        // Default options
        $options = '-loglevel quiet -show_format -show_streams -print_format json';
        $options .= ' -pretty';

        // Run the ffprobe, save the JSON output then decode
        $json = json_decode(shell_exec(
            sprintf('ffprobe %s %s', $options, escapeshellarg($path))
        ), false, 512, JSON_THROW_ON_ERROR);

        if ($json === null) {
            throw new RuntimeException('FFProbe failed to run.');
        }

        return new VideoCodecEntity((array)$json->streams[0]);
    }

    public function create(VideoCodecEntity $videoCodec, array $pathinfo, int $fileSize, string $hash): FileModel
    {
        return FileModel::updateOrCreate(
            [
                'storage' => 'local',
                'hash' => $hash,
                'name' => $pathinfo['basename'],
                'path' => $pathinfo['dirname'],
            ],
            [
                'type' => $videoCodec->get('codec_type'),
                'extension' => $pathinfo['extension'],
                'format' => $videoCodec->get('codec_tag_string'),
                'size' => $fileSize,
                'ratio' => $videoCodec->get('display_aspect_ratio'),
                'width' => $videoCodec->get('width'),
                'height' => $videoCodec->get('height'),
                'metadata' => $videoCodec->toArray(),
            ]
        );
    }
}
