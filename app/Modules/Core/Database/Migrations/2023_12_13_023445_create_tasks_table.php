<?php

use App\Modules\Core\Models\Task;
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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();
            $table->morphs('model');
            $table->string('task')->index();
            $table->string('state_code')->default(InitState::STATE_CODE)->index();

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
        Schema::dropIfExists('tasks');
    }
};
