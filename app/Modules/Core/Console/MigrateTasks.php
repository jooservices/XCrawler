<?php

namespace App\Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTasks extends Command
{
    public const COMMAND = 'migrate:tasks';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate tasks from MongoDB to MySQL.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        DB::connection('mongodb')->collection('tasks')->cursor()->each(function ($task) {
            DB::table('tasks')->insert([
                'uuid' => $task['uuid'],
                'model_id' => $task['model_id'],
                'model_type' => $task['model_type'],
                'task' => $task['task'],
                'state_code' => $task['state_code'],
                'created_at' => $task['created_at'],
                'updated_at' => $task['updated_at'],
            ]);
        });
    }
}
