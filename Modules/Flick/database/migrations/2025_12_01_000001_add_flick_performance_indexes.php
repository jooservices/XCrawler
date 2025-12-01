<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flick_crawl_tasks', function (Blueprint $table) {
            // Worker query optimization (WHERE status = ? ORDER BY priority DESC, created_at ASC)
            $table->index(['status', 'priority', 'created_at'], 'idx_worker_query');

            // Task lookup optimization (WHERE contact_nsid = ? AND type = ? AND status = ?)
            $table->index(['contact_nsid', 'type', 'status'], 'idx_task_lookup');

            // Callback lookup optimization (WHERE hub_request_id = ?)
            $table->index(['hub_request_id'], 'idx_hub_request');
        });

        Schema::table('flick_photos', function (Blueprint $table) {
            // Download status queries (WHERE owner_nsid = ? AND downloaded_at IS NULL)
            $table->index(['owner_nsid', 'downloaded_at'], 'idx_download_status');
        });

        Schema::table('flick_contacts', function (Blueprint $table) {
            // Monitored contacts query (WHERE is_monitored = 1)
            $table->index(['is_monitored'], 'idx_monitored');

            // Profile URL lookup (WHERE profile_url = ?)
            $table->index(['profile_url'], 'idx_profile_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flick_crawl_tasks', function (Blueprint $table) {
            $table->dropIndex('idx_worker_query');
            $table->dropIndex('idx_task_lookup');
            $table->dropIndex('idx_hub_request');
        });

        Schema::table('flick_photos', function (Blueprint $table) {
            $table->dropIndex('idx_download_status');
        });

        Schema::table('flick_contacts', function (Blueprint $table) {
            $table->dropIndex('idx_monitored');
            $table->dropIndex('idx_profile_url');
        });
    }
};
