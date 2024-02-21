<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Entities\VideoCodecEntity;
use App\Modules\Core\Jobs\FileScanJob;
use Illuminate\Support\Facades\File;
use RuntimeException;

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

    public function scan(string $path, array $allowedExtensions = self::ALLOWED_EXTENSIONS)
    {
        $files = File::files($path);
        foreach ($files as $file) {
            if ($file->isDir()) {
                $this->scan($file->getPathname());
                return;
            }

            if (in_array(strtolower($file->getExtension()), self::ALLOWED_EXTENSIONS)) {
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

        return new VideoCodecEntity((array) $json->streams[0]);
    }
}