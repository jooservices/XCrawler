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
        Schema::create('onejav', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->unique();

            $table->string('url')->unique();
            $table->string('cover')->nullable();
            $table->string('dvd_id')->index();
            $table->float('size')->nullable();
            $table->date('date')->nullable();
            $table->json('genres')->nullable();
            $table->json('performers')->nullable();
            $table->text('description')->nullable();
            $table->string('torrent')->nullable();
            $table->json('gallery')->nullable();

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
        Schema::dropIfExists('onejav');
    }
};
