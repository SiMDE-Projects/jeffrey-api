<?php

namespace App\Providers;

use App\Auth\CasUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Auth::provider('cas', function($app, array $config) {
            return new CasUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
