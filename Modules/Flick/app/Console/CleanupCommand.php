<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupCommand extends Command
{
    protected $signature = 'flick:cleanup 
                            {--dry-run : Preview deletions without executing}
                            {--older-than=7d : Delete failed tasks older than this (e.g., 3d, 12h)}';

    protected $description = 'Clean up ghost tasks and old failed tasks';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $olderThan = $this->option('older-than');

        $this->info("Starting task cleanup" . ($dryRun ? " (DRY RUN)" : ""));
        $this->newLine();

        // Parse --older-than duration
        $failedTaskAge = $this->parseDuration($olderThan);

        if (!$failedTaskAge) {
            $this->error("Invalid duration format: $olderThan");
            $this->info("Examples: 3d (3 days), 12h (12 hours), 30m (30 minutes)");
            return 1;
        }

        $stats = [
            'ghost_tasks' => 0,
            'old_failed' => 0,
            'total' => 0,
        ];

        // 1. Clean up ghost tasks (queued_at_hub for > 30 minutes)
        $this->info("🔍 Finding ghost tasks (queued_at_hub > 30 minutes)...");
        $ghostCutoff = Carbon::now()->subMinutes(30);

        $ghostTasks = \Modules\Flick\Models\FlickCrawlTask::where('status', 'queued_at_hub')
            ->where('updated_at', '<', $ghostCutoff)
            ->get();

        $stats['ghost_tasks'] = $ghostTasks->count();

        if ($stats['ghost_tasks'] > 0) {
            $this->table(
                ['ID', 'Type', 'NSID', 'Hub Request ID', 'Updated At'],
                $ghostTasks->map(fn($t) => [
                    $t->id,
                    $t->type,
                    $t->contact_nsid,
                    $t->hub_request_id ?? 'N/A',
                    $t->updated_at->diffForHumans(),
                ])->toArray()
            );

            if (!$dryRun) {
                $deleted = \Modules\Flick\Models\FlickCrawlTask::where('status', 'queued_at_hub')
                    ->where('updated_at', '<', $ghostCutoff)
                    ->delete();
                $this->info("✅ Deleted $deleted ghost tasks");
            }
        } else {
            $this->info("No ghost tasks found.");
        }

        $this->newLine();

        // 2. Clean up old failed tasks
        $this->info("🔍 Finding old failed tasks (older than $olderThan)...");
        $failedCutoff = Carbon::now()->sub($failedTaskAge);

        $oldFailedTasks = \Modules\Flick\Models\FlickCrawlTask::where('status', 'failed')
            ->where('updated_at', '<', $failedCutoff)
            ->get();

        $stats['old_failed'] = $oldFailedTasks->count();

        if ($stats['old_failed'] > 0) {
            $this->info("Found {$stats['old_failed']} old failed tasks");

            if (!$dryRun) {
                $deleted = \Modules\Flick\Models\FlickCrawlTask::where('status', 'failed')
                    ->where('updated_at', '<', $failedCutoff)
                    ->delete();
                $this->info("✅ Deleted $deleted old failed tasks");
            }
        } else {
            $this->info("No old failed tasks found.");
        }

        $this->newLine();

        // Summary
        $stats['total'] = $stats['ghost_tasks'] + $stats['old_failed'];

        $this->info("📊 Cleanup Summary:");
        $this->table(
            ['Category', 'Count'],
            [
                ['Ghost tasks (queued > 30min)', $stats['ghost_tasks']],
                ['Old failed tasks', $stats['old_failed']],
                ['Total', $stats['total']],
            ]
        );

        if ($dryRun && $stats['total'] > 0) {
            $this->warn("This was a dry run. Re-run without --dry-run to delete these tasks.");
        }

        return 0;
    }

    protected function parseDuration(string $duration): ?\DateInterval
    {
        // Parse duration like "3d", "12h", "30m"
        if (preg_match('/^(\d+)([dhm])$/', $duration, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            return match ($unit) {
                'd' => new \DateInterval("P{$value}D"),
                'h' => new \DateInterval("PT{$value}H"),
                'm' => new \DateInterval("PT{$value}M"),
                default => null,
            };
        }

        return null;
    }
}
