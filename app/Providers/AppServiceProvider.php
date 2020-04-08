<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\BlockcypherAPI;

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
        $this->app->singleton(BlockcypherAPI::class, function ($app) {
            return new BlockcypherAPI();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
