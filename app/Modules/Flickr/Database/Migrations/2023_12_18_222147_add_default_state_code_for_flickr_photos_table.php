<?php

use App\Modules\Core\Services\States;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('flickr_photos')->update([
            'state_code' => States::STATE_INIT
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::table('flickr_photos')->update([
            'state_code' => null
        ]);
    }
};
