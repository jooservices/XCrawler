<?php

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
        Schema::create('flickr_photos', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();

            $table->string('owner')->index();
            $table->foreign('owner')->references('nsid')->on('flickr_contacts');

            $table->unique(['owner', 'id']);

            $table->integer('farm')->nullable();
            $table->boolean('isfamily')->nullable();
            $table->boolean('isfriend')->nullable();
            $table->boolean('ispublic')->nullable();

            $table->string('secret')->nullable();
            $table->string('server')->nullable();
            $table->string('title')->nullable();

            $table->json('sizes')->nullable();

            $table->dateTime('dateuploaded')->nullable();
            $table->integer('views')->nullable();
            $table->char('media', 10)->nullable();

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
        Schema::dropIfExists('flickr_photos');
    }
};
