<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flickr_google_photo_albums', function (Blueprint $table) {
            $table->id();

            $table->string('album_id');
            $table->foreignId('flickr_photoset_id')->nullable()->constrained('flickr_photosets');

            $table->string('title')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_photo_albums');
    }
};
