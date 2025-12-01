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
        Schema::create('flick_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('nsid')->unique();
            $table->string('username')->nullable();
            $table->string('realname')->nullable();
            $table->string('location')->nullable();
            $table->string('iconserver')->nullable();
            $table->integer('iconfarm')->nullable();
            $table->integer('photos_count')->default(0);
            $table->integer('contacts_count')->default(0);
            $table->string('crawl_status')->default('pending'); // pending, processing_photos, processing_faves, processing_contacts, completed
            $table->timestamp('last_crawled_at')->nullable();
            $table->boolean('is_monitored')->default(false);
            $table->string('profile_url')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('flick_photos', function (Blueprint $table) {
            $table->id();
            $table->string('flickr_id')->unique();
            $table->string('owner_nsid');
            $table->string('title')->nullable();
            $table->string('secret')->nullable();
            $table->string('server')->nullable();
            $table->integer('farm')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('has_comment')->default(false);
            $table->json('sizes_json')->nullable();
            $table->boolean('is_downloaded')->default(false);
            $table->string('local_path')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('owner_nsid');
        });

        Schema::create('flick_crawl_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('contact_nsid');
            $table->string('type'); // FETCH_PHOTOS, FETCH_FAVES, FETCH_CONTACTS
            $table->integer('page')->default(1);
            $table->string('status')->default('pending'); // pending, queued_at_hub, completed, failed
            $table->string('hub_request_id')->nullable();
            $table->integer('priority')->default(0);
            $table->integer('depth')->default(0);
            $table->json('payload')->nullable(); // Store extra data (e.g. original URL, original action)
            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flick_crawl_tasks');
        Schema::dropIfExists('flick_photos');
        Schema::dropIfExists('flick_contacts');
    }
};
