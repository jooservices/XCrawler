<?php

use App\Modules\Core\StateMachine\Task\InitState;
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
        Schema::create('files', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();
            $table->string('storage')->default('local');
            $table->string('name');
            $table->string('path');
            $table->string('type');
            $table->string('extension');
            $table->string('format')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('metadata')->nullable();

            $table->string('state_code')->default(InitState::STATE_CODE)->index();

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
        Schema::dropIfExists('files');
    }
};
