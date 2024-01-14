<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flickr_photosets', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();

            $table->string('owner')->index()->nullable();
            $table->foreign('owner')->references('nsid')->on('flickr_contacts');

            $table->string('primary')->nullable();
            $table->string('secret')->nullable();
            $table->string('server')->nullable();
            $table->integer('farm')->nullable();
            $table->integer('count_photos')->nullable();
            $table->integer('count_videos')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->integer('photos')->nullable();

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
        Schema::dropIfExists('flickr_photosets');
    }
};
