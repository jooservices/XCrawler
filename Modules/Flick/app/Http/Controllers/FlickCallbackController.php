<?php

namespace Modules\Flick\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FlickCallbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("FlickCallback: Received payload", $request->all());
        $data = $request->validate([
            'request_id' => 'required|integer',
            'status' => 'required|string',
            'result' => 'nullable|array',
            'data' => 'nullable|array', // Add data key
            'error' => 'nullable|string',
        ]);

        $task = \Modules\Flick\Models\FlickCrawlTask::where('hub_request_id', $data['request_id'])->first();

        if (!$task) {
            \Illuminate\Support\Facades\Log::warning("FlickCallback: Task not found for hub_request_id: {$data['request_id']}. Available tasks: " . \Modules\Flick\Models\FlickCrawlTask::whereNotNull('hub_request_id')->pluck('hub_request_id')->implode(', '));
            return response()->json([
                'success' => false,
                'error' => 'task_not_found',
                'message' => 'Task not found',
                'request_id' => $data['request_id']
            ], 200);
        }

        if ($data['status'] === 'failed') {
            $task->update(['status' => 'failed']);
            \Illuminate\Support\Facades\Log::error("FlickCallback: Task failed", ['task_id' => $task->id, 'error' => $data['error'] ?? 'Unknown']);
            return response()->json([
                'success' => true,
                'message' => 'Task marked as failed',
                'task_id' => $task->id
            ], 200);
        }

        try {
            // Support both 'result' and 'data' keys
            $result = $data['result'] ?? $data['data'] ?? null;

            if (!$result) {
                \Illuminate\Support\Facades\Log::error("FlickCallback: Missing 'result' or 'data' in payload", ['payload' => $data]);
                return response()->json([
                    'success' => false,
                    'error' => 'missing_result',
                    'message' => 'Missing result or data in payload',
                    'request_id' => $data['request_id']
                ], 200);
            }

            $this->processResult($task, $result);
            $task->update(['status' => 'completed']);

            // Trigger next steps if needed (recursion logic can be here or in a separate job/command that monitors completed tasks)
            // For now, we just save data. The 'Manager' command will pick up next steps.

            return response()->json([
                'success' => true,
                'message' => 'Processed',
                'task_id' => $task->id
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("FlickCallback: Processing error", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'processing_error',
                'message' => $e->getMessage(),
                'task_id' => $task->id
            ], 200);
        }
    }

    protected function processResult($task, $result)
    {
        if ($task->type === 'FETCH_CONTACTS') {
            $this->processContacts($task, $result);
        } elseif ($task->type === 'FETCH_PHOTOS') {
            $this->processPhotos($task, $result);
        } elseif ($task->type === 'FETCH_FAVES') {
            $this->processContacts($task, $result); // Faves are just contacts
        } elseif ($task->type === 'RESOLVE_USER') {
            $this->processUserResolution($task, $result);
        }
    }

    protected function processUserResolution($task, $result)
    {
        // Result structure: { "user": { "id": "...", "username": { "_content": "..." } } }
        if (isset($result['user']['id'])) {
            $nsid = $result['user']['id'];
            $username = $result['user']['username']['_content'] ?? null;
            $url = $task->payload['url'] ?? null;

            // Update or Create Contact
            $contact = \Modules\Flick\Models\FlickContact::firstOrCreate(
                ['nsid' => $nsid],
                ['username' => $username]
            );

            if ($url) {
                $contact->update(['profile_url' => $url]);
            }

            // Trigger Original Action
            $originalAction = $task->payload['original_action'] ?? 'FETCH_CONTACTS';

            if ($originalAction === 'LIKE') {
                $contact->update(['is_monitored' => true]);
                \Illuminate\Support\Facades\Log::info("Resolved user {$url} to {$nsid}. Marked as LIKED.");
                // Optionally notify Telegram that resolution and like is complete
            } else {
                \Modules\Flick\Models\FlickCrawlTask::create([
                    'contact_nsid' => $nsid,
                    'type' => $originalAction,
                    'page' => 1,
                    'status' => 'pending',
                    'priority' => 100,
                    'depth' => 0,
                ]);
                \Illuminate\Support\Facades\Log::info("Resolved user {$url} to {$nsid}. Triggered {$originalAction}.");
            }
        } else {
            \Illuminate\Support\Facades\Log::error("Failed to resolve user for task {$task->id}", ['result' => $result]);
        }
    }

    protected function processContacts($task, $result)
    {
        // Result structure depends on flickr.contacts.getList or getPublicList
        // Usually: { contacts: { contact: [...] } }
        $contacts = $result['contacts']['contact'] ?? [];
        $maxDepth = config('flick.max_depth', 3); // Default max depth 3

        foreach ($contacts as $c) {
            \Modules\Flick\Models\FlickContact::updateOrCreate(
                ['nsid' => $c['nsid']],
                [
                    'username' => $c['username'] ?? null,
                    'realname' => $c['realname'] ?? null,
                    'location' => $c['location'] ?? null,
                    'iconserver' => $c['iconserver'] ?? null,
                    'iconfarm' => $c['iconfarm'] ?? null,
                    'photos_count' => $c['photos_count'] ?? 0, // Sometimes not present in list
                ]
            );

            // Recursion: Queue FETCH_PHOTOS for this contact
            // Depth increases by 1 relative to the current task (which was FETCH_CONTACTS of someone)
            // Wait, if I am fetching contacts of Root (Depth 0), these contacts are Depth 1.
            // So their photos are Depth 1 content.
            // So new task depth = task->depth + 1.

            // Constraint: Only recurse if depth is reasonable (e.g., < 3). User said "mở rộng dần".
            // Let's set a soft limit or just let it run. User didn't specify limit.

            if ($task->depth + 1 <= $maxDepth) {
                \Modules\Flick\Models\FlickCrawlTask::firstOrCreate(
                    [
                        'contact_nsid' => $c['nsid'],
                        'type' => 'FETCH_PHOTOS',
                        'page' => 1,
                    ],
                    [
                        'status' => 'pending',
                        'priority' => 10, // Lower priority than current level
                        'depth' => $task->depth + 1,
                    ]
                );
            }
        }
    }

    protected function processPhotos($task, $result)
    {
        // Result: { photos: { photo: [...], page: 1, pages: 10, ... } }
        $photos = $result['photos']['photo'] ?? [];
        $maxDepth = config('flick.max_depth', 3);

        foreach ($photos as $p) {
            // Extract URLs from extras to populate sizes_json (simulated structure)
            $sizes = [];
            if (isset($p['url_o']))
                $sizes['original'] = $p['url_o'];
            if (isset($p['url_k']))
                $sizes['large_2048'] = $p['url_k']; // 2048
            if (isset($p['url_h']))
                $sizes['large_1600'] = $p['url_h']; // 1600
            if (isset($p['url_l']))
                $sizes['large'] = $p['url_l']; // 1024
            if (isset($p['url_c']))
                $sizes['medium_800'] = $p['url_c']; // 800
            if (isset($p['url_z']))
                $sizes['medium_640'] = $p['url_z']; // 640

            \Modules\Flick\Models\FlickPhoto::updateOrCreate(
                ['flickr_id' => $p['id']],
                [
                    'owner_nsid' => $task->contact_nsid, // Or $p['owner']
                    'title' => $p['title'] ?? null,
                    'secret' => $p['secret'] ?? null,
                    'server' => $p['server'] ?? null,
                    'farm' => $p['farm'] ?? null,
                    'is_primary' => $p['isprimary'] ?? 0,
                    'has_comment' => $p['has_comment'] ?? 0,
                    // 'posted_at' => ... need extra info or different call
                    'sizes_json' => !empty($sizes) ? $sizes : null, // Save extracted URLs
                ]
            );
        }

        $page = $result['photos']['page'] ?? 1;
        $pages = $result['photos']['pages'] ?? 1;

        if ($page < $pages) {
            \Modules\Flick\Models\FlickCrawlTask::create([
                'contact_nsid' => $task->contact_nsid,
                'type' => 'FETCH_PHOTOS',
                'page' => $page + 1,
                'status' => 'pending',
                'priority' => $task->priority,
                'depth' => $task->depth,
            ]);
        } else {
            // Finished all photos for this user.
            // 1. Notify Telegram
            $totalPhotos = $result['photos']['total'] ?? count($photos);
            $contact = \Modules\Flick\Models\FlickContact::where('nsid', $task->contact_nsid)->first();
            $name = $contact ? ($contact->username ?? $contact->realname ?? $task->contact_nsid) : $task->contact_nsid;

            $msg = "Contact {$name} ({$task->contact_nsid}) has {$totalPhotos} photos.";
            (new \Modules\Flick\Services\TelegramService())->notify($msg);

            // 2. Recursion: Queue FETCH_CONTACTS and FETCH_FAVES for this user
            // This allows expanding the graph.
            // Depth is same as this user (which is task->depth).
            // Wait, if task->depth is 1 (Contact A), we are now fetching Contact A's contacts.
            // Those contacts will be Depth 2.
            // So we queue FETCH_CONTACTS for Contact A at Depth 1.
            // When that runs, it will create FETCH_PHOTOS at Depth 2. Correct.

            if ($task->depth < $maxDepth) {
                \Modules\Flick\Models\FlickCrawlTask::firstOrCreate(
                    [
                        'contact_nsid' => $task->contact_nsid,
                        'type' => 'FETCH_CONTACTS',
                        'page' => 1,
                    ],
                    [
                        'status' => 'pending',
                        'priority' => 5,
                        'depth' => $task->depth,
                    ]
                );

                \Modules\Flick\Models\FlickCrawlTask::firstOrCreate(
                    [
                        'contact_nsid' => $task->contact_nsid,
                        'type' => 'FETCH_FAVES',
                        'page' => 1,
                    ],
                    [
                        'status' => 'pending',
                        'priority' => 5,
                        'depth' => $task->depth,
                    ]
                );

                // Auto-Download for Monitored Contacts
                $contact = \Modules\Flick\Models\FlickContact::where('nsid', $task->contact_nsid)->first();
                if ($contact && $contact->is_monitored) {
                    \Modules\Flick\Models\FlickCrawlTask::create([
                        'contact_nsid' => $task->contact_nsid,
                        'type' => 'DOWNLOAD_PHOTOS',
                        'page' => 1,
                        'status' => 'pending',
                        'priority' => 90, // Very high priority
                        'depth' => $task->depth,
                    ]);
                }
            }
        }
    }

    protected function processSizes($task, $result)
    {
        // Result: { sizes: { size: [...] } }
        $sizes = $result['sizes']['size'] ?? [];

        // We need to find which photo this belongs to.
        // The task should have the photo ID? 
        // Ah, FETCH_SIZES task needs a 'photo_id' or we store it in 'contact_nsid' field (hacky) or add a field.
        // Let's assume we added 'flickr_id' to task or we use 'contact_nsid' as 'flickr_id' for this task type.

        // Actually, for download, we iterate photos.
        // If we use a task for getSizes, we need to know the photo ID.
        // Let's assume we update the photo directly.

        // Wait, how do we know the photo ID from the result?
        // flickr.photos.getSizes result does NOT always contain the photo ID in the body, only the sizes.
        // So we MUST rely on the task to tell us which photo it was.

        // For now, let's assume we don't use FETCH_SIZES task yet, or we handle it later.
        // But if we do:
        $photoId = $task->contact_nsid; // Using this field to store photo ID for this task type

        $photo = \Modules\Flick\Models\FlickPhoto::where('flickr_id', $photoId)->first();
        if ($photo) {
            $photo->update(['sizes_json' => $sizes]);
        }
    }
}
