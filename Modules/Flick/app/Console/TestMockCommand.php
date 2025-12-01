<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TestMockCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:test-mock {nsid : The NSID to simulate} {type=photos : contacts or photos}';

    protected $description = 'Simulate a FlickrHub callback for testing';

    public function handle()
    {
        $nsid = $this->argument('nsid');
        $type = $this->argument('type');

        $this->info("Simulating FlickrHub callback for {$nsid} ({$type})...");

        // 1. Create a fake task in DB so the callback has something to process
        $task = \Modules\Flick\Models\FlickCrawlTask::firstOrCreate(
            [
                'contact_nsid' => $nsid,
                'type' => $type === 'contacts' ? 'FETCH_CONTACTS' : 'FETCH_PHOTOS',
                'page' => 1,
            ],
            [
                'status' => 'queued_at_hub',
                'priority' => 100,
                'depth' => 0,
            ]
        );

        // Ensure status is queued_at_hub and set hub_request_id
        $task->update([
            'status' => 'queued_at_hub',
            'hub_request_id' => $task->id // Use task ID as hub_request_id for mock
        ]);

        $this->info("Task created/updated: ID {$task->id}");

        // 2. Prepare Mock Payload
        $payload = [
            'request_id' => $task->id, // We use task ID as request ID for simplicity in this mock
            'status' => 'success',
            'result' => []
        ];

        if ($type === 'contacts') {
            $payload['result'] = [
                'contacts' => [
                    'contact' => [
                        [
                            'nsid' => '12345678@N00',
                            'username' => 'MockUser1',
                            'realname' => 'Mock User One',
                            'location' => 'Mock City'
                        ],
                        [
                            'nsid' => '87654321@N00',
                            'username' => 'MockUser2',
                            'realname' => 'Mock User Two',
                            'location' => 'Mock Town'
                        ]
                    ],
                    'total' => 2,
                    'page' => 1,
                    'pages' => 1
                ]
            ];
        } else {
            // Photos
            $payload['result'] = [
                'photos' => [
                    'photo' => [
                        [
                            'id' => '1001',
                            'secret' => 'abc1',
                            'server' => '111',
                            'farm' => 1,
                            'title' => 'Mock Photo 1',
                            'ispublic' => 1,
                            'isfriend' => 0,
                            'isfamily' => 0,
                            'dateupload' => time(),
                            'datetaken' => date('Y-m-d H:i:s'),
                            'ownername' => 'Mock Owner',
                            'views' => 100,
                            'tags' => 'mock,test',
                            'media' => 'photo'
                        ],
                        [
                            'id' => '1002',
                            'secret' => 'abc2',
                            'server' => '222',
                            'farm' => 2,
                            'title' => 'Mock Photo 2',
                            'ispublic' => 1,
                            'isfriend' => 0,
                            'isfamily' => 0,
                            'dateupload' => time(),
                            'datetaken' => date('Y-m-d H:i:s'),
                            'ownername' => 'Mock Owner',
                            'views' => 200,
                            'tags' => 'mock,test',
                            'media' => 'photo'
                        ]
                    ],
                    'total' => 2,
                    'page' => 1,
                    'pages' => 1
                ]
            ];
        }

        // 3. Send Request to Local Callback
        $url = 'http://localhost:8000/api/flick/callback'; // Assuming served locally
        // Or use app()->handle() to bypass HTTP, but HTTP is more realistic.
        // Let's try internal dispatch first to avoid network issues.

        $request = \Illuminate\Http\Request::create(
            '/api/flick/callback',
            'POST',
            $payload
        );

        $this->info("Dispatching request to controller...");

        $response = app()->handle($request);

        $this->info("Response Status: " . $response->getStatusCode());
        $this->info("Response Content: " . $response->getContent());

        if ($response->getStatusCode() === 200) {
            $this->info("Mock Test Successful! Check database and Telegram.");
        } else {
            $this->error("Mock Test Failed.");
        }
    }
}
