<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->environment(['production'])) {
            $this->testMySqlConnection();
            $this->testRedisConnection();
        }
    }

    private function testMySqlConnection()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            report("Could not connect to the database.  Please check your configuration. error:" . $e);
            die;
        }
    }

    private function testRedisConnection()
    {
        try {
            Cache::get('dummy');
        } catch (\Exception $e) {
            report("Could not connect to the database.  Please check your configuration. error:" . $e);
            die;
        }
    }
}
