<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Entities\VideoCodecEntity;
use App\Modules\Core\Http\Requests\CreateFileRequest;
use App\Modules\Core\Services\FileScannerService;

class FileController extends ApiController
{
    public function create(CreateFileRequest $request, FileScannerService $scannerService)
    {
        $video = new VideoCodecEntity(
            json_decode($request->input('data'), true, 512, JSON_THROW_ON_ERROR)['streams'][0]
        );
        $hash = $request->input('hash');
        $hash = trim(str_replace($request->input('file'), '', $hash));

        $scannerService->create($video, pathinfo($request->input('file')), $request->input('size'), $hash);
    }
}
