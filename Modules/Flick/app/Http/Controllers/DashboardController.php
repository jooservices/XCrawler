<?php

namespace Modules\Flick\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Flick\Models\FlickContact;
use Modules\Flick\Models\FlickPhoto;
use Modules\Flick\Models\FlickCrawlTask;

class DashboardController extends Controller
{
    public function stats()
    {
        // Calculate missed files (downloaded but file doesn't exist)
        $downloadedPhotos = FlickPhoto::whereNotNull('downloaded_at')->get();
        $missedCount = $downloadedPhotos->filter(function ($photo) {
            $path = storage_path('app/public/' . $photo->local_path);
            return !file_exists($path);
        })->count();

        return response()->json([
            'contacts' => [
                'total' => FlickContact::count(),
                'monitored' => FlickContact::where('is_monitored', true)->count(),
            ],
            'photos' => [
                'total' => FlickPhoto::count(),
                'downloaded' => FlickPhoto::whereNotNull('downloaded_at')->count(),
                'missed' => $missedCount,
            ],
            'tasks' => [
                'pending' => FlickCrawlTask::where('status', 'pending')->count(),
                'processing' => FlickCrawlTask::whereIn('status', ['processing', 'queued_at_hub'])->count(),
                'completed' => FlickCrawlTask::where('status', 'completed')->count(),
                'failed' => FlickCrawlTask::where('status', 'failed')->count(),
            ]
        ]);
    }

    public function tasks(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $tasks = FlickCrawlTask::with('contact:id,nsid,username,realname')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'type' => $task->type,
                    'status' => $task->status,
                    'contact' => $task->contact ? ($task->contact->username ?? $task->contact->realname ?? $task->contact_nsid) : $task->contact_nsid,
                    'updated_at' => $task->updated_at->diffForHumans(),
                    'retry_count' => $task->retry_count,
                ];
            }),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'total' => $tasks->total(),
        ]);
    }

    public function contacts(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 50);

        $contacts = FlickContact::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $contacts->items(),
            'current_page' => $contacts->currentPage(),
            'last_page' => $contacts->lastPage(),
            'total' => $contacts->total(),
        ]);
    }

    public function photos(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 50);

        $photos = FlickPhoto::with('owner:id,nsid,username')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $photos->items(),
            'current_page' => $photos->currentPage(),
            'last_page' => $photos->lastPage(),
            'total' => $photos->total(),
        ]);
    }

    public function execute(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:crawl,retry,cleanup,download',
            'params' => 'nullable|array',
        ]);

        $command = $request->input('command');
        $params = $request->input('params', []);

        $artisanCommand = "flick:$command";

        // Map params to artisan arguments/options
        $args = [];
        if ($command === 'crawl' && !empty($params['url'])) {
            $args['nsid'] = $params['url'];
        }
        if ($command === 'download') {
            if (empty($params['nsid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'NSID or URL is required for download'
                ], 400);
            }
            $args['nsid'] = $params['nsid'];
            if (!empty($params['limit'])) {
                $args['--limit'] = (int) $params['limit'];
            }
        }
        if ($command === 'cleanup' && !empty($params['older_than'])) {
            $args['--older-than'] = $params['older_than'];
        }
        if ($command === 'retry' && !empty($params['all'])) {
            $args['--all'] = true;
        }

        try {
            Artisan::call($artisanCommand, $args);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => "Command executed successfully",
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Command failed: " . $e->getMessage()
            ], 500);
        }
    }
}
