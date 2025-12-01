<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;

class RetryCommand extends Command
{
    protected $signature = 'flick:retry 
                            {--task-id= : Retry a specific task by ID}
                            {--all : Retry all eligible failed tasks}';

    protected $description = 'Retry failed tasks with exponential backoff';

    public function handle()
    {
        $taskId = $this->option('task-id');
        $retryAll = $this->option('all');

        if ($taskId) {
            return $this->retrySpecificTask($taskId);
        }

        if ($retryAll) {
            return $this->retryAllEligibleTasks();
        }

        // Default: Show status and ask
        return $this->showRetryStatus();
    }

    protected function retrySpecificTask(int $taskId)
    {
        $task = \Modules\Flick\Models\FlickCrawlTask::find($taskId);

        if (!$task) {
            $this->error("Task not found: $taskId");
            return 1;
        }

        if ($task->status !== 'failed') {
            $this->error("Task $taskId is not in 'failed' status (current: {$task->status})");
            return 1;
        }

        if ($task->retry_count >= $task->max_retries) {
            $this->error("Task $taskId has exceeded max retries ({$task->max_retries})");
            return 1;
        }

        $task->update([
            'status' => 'pending',
            'retry_count' => $task->retry_count + 1,
        ]);

        $this->info("✅ Task $taskId marked for retry (attempt {$task->retry_count}/{$task->max_retries})");
        return 0;
    }

    protected function retryAllEligibleTasks()
    {
        $this->info("Finding eligible tasks for retry...");

        // Tasks that:
        // 1. Status = 'failed'
        // 2. retry_count < max_retries
        // 3. failed_at is past the backoff window

        $eligibleTasks = \Modules\Flick\Models\FlickCrawlTask::where('status', 'failed')
            ->whereColumn('retry_count', '<', 'max_retries')
            ->get()
            ->filter(function ($task) {
                return $this->isReadyForRetry($task);
            });

        $count = $eligibleTasks->count();

        if ($count === 0) {
            $this->info("No eligible tasks found for retry.");
            return 0;
        }

        $this->info("Found $count eligible tasks.");
        $this->table(
            ['ID', 'Type', 'NSID', 'Retry', 'Failed At', 'Error'],
            $eligibleTasks->map(fn($t) => [
                $t->id,
                $t->type,
                substr($t->contact_nsid, 0, 20),
                "{$t->retry_count}/{$t->max_retries}",
                $t->failed_at ? $t->failed_at->diffForHumans() : 'N/A',
                substr($t->last_error ?? 'Unknown', 0, 40),
            ])->toArray()
        );

        if (!$this->confirm("Retry all $count tasks?", true)) {
            $this->info("Cancelled.");
            return 0;
        }

        foreach ($eligibleTasks as $task) {
            $task->update([
                'status' => 'pending',
                'retry_count' => $task->retry_count + 1,
            ]);
        }

        $this->info("✅ Marked $count tasks for retry.");
        return 0;
    }

    protected function showRetryStatus()
    {
        $failedTasks = \Modules\Flick\Models\FlickCrawlTask::where('status', 'failed')->get();

        $eligible = $failedTasks->filter(fn($t) => $t->retry_count < $t->max_retries && $this->isReadyForRetry($t));
        $exhausted = $failedTasks->filter(fn($t) => $t->retry_count >= $t->max_retries);
        $backoff = $failedTasks->filter(fn($t) => $t->retry_count < $t->max_retries && !$this->isReadyForRetry($t));

        $this->info("📊 Retry Status:");
        $this->table(
            ['Category', 'Count'],
            [
                ['Eligible for retry (ready now)', $eligible->count()],
                ['In backoff window (waiting)', $backoff->count()],
                ['Retry exhausted (max attempts)', $exhausted->count()],
                ['Total failed', $failedTasks->count()],
            ]
        );

        if ($eligible->count() > 0) {
            $this->newLine();
            $this->info("Run 'php artisan flick:retry --all' to retry eligible tasks.");
        }

        return 0;
    }

    protected function isReadyForRetry($task): bool
    {
        if (!$task->failed_at) {
            return true; // No backoff set, ready immediately
        }

        // Exponential backoff: 5min, 15min, 60min
        $backoffMinutes = match ($task->retry_count) {
            0 => 5,
            1 => 15,
            2 => 60,
            default => 120,
        };

        $backoffEnd = Carbon::parse($task->failed_at)->addMinutes($backoffMinutes);
        return Carbon::now()->isAfter($backoffEnd);
    }
}
