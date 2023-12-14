<?php

use App\Modules\Flickr\Models\FlickrContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flickr_contacts', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();
            $table->string('nsid')->unique();
            $table->string('username')->nullable();
            $table->string('realname')->nullable();
            $table->boolean('friend')->nullable();
            $table->boolean('family')->nullable();
            $table->boolean('ignored')->nullable();
            $table->boolean('rev_ignored')->nullable();
            $table->string('iconserver')->nullable();
            $table->string('iconfarm')->nullable();
            $table->string('path_alias')->nullable();
            $table->boolean('has_stats')->nullable();
            $table->char('gender', 1)->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('photosurl')->nullable();
            $table->string('profileurl')->nullable();
            $table->string('mobileurl')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flickr_contacts');
    }
};
