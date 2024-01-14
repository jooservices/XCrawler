<?php

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
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
        Integration::where('state_code', 'COMPLETED')
            ->update(['state_code' => CompletedState::class]);
    }
};
