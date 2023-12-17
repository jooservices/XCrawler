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
        Schema::create('flickr_photosets_photos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('photoset_id')->index();
            $table->foreign('photoset_id')->references('id')->on('flickr_photosets');

            $table->unsignedBigInteger('photo_id')->index();
            $table->foreign('photo_id')->references('id')->on('flickr_photos');

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
        Schema::dropIfExists('flickr_photosets_photos');
    }
};
