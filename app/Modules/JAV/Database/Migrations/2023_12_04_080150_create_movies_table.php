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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();

            $table->string('dvd_id')->unique();
            $table->string('url')->nullable();
            $table->string('cover')->nullable();
            $table->string('torrent')->nullable();
            $table->float('size')->nullable();
            $table->json('gallery')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('movie_genre', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->foreignId('movie_genre_id')->constrained('genres')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('movie_performer', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->foreignId('movie_performer_id')->constrained('performers')->onDelete('cascade');

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
        Schema::dropIfExists('movies');
    }
};
