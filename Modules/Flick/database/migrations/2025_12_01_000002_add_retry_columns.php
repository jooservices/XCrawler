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
            $table->integer('retry_count')->default(0)->after('status');
            $table->integer('max_retries')->default(3)->after('retry_count');
            $table->text('last_error')->nullable()->after('max_retries');
            $table->timestamp('failed_at')->nullable()->after('last_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flick_crawl_tasks', function (Blueprint $table) {
            $table->dropColumn(['retry_count', 'max_retries', 'last_error', 'failed_at']);
        });
    }
};
