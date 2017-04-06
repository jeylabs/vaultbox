<?php

namespace JeyLabs\Vaultbox;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;


/**
 * Class VaultboxServiceProvider
 * @package Jeylabs\Vaultbox
 */
class VaultboxServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Config::get('vaultbox.use_package_routes'))
            include __DIR__ . '/routes.php';

        $this->loadTranslationsFrom(__DIR__.'/lang', 'vaultbox');

        $this->loadViewsFrom(__DIR__.'/views', 'vaultbox');

        $this->publishes([
            __DIR__ . '/config/vaultbox.php' => base_path('config/gitvaultbox.php'),
        ], 'Vaultbox_config');

        $this->publishes([      
            __DIR__.'/../public' => public_path('vendor/vaultbox'),     
        ], 'Vaultbox_public');

        $this->publishes([
            __DIR__.'/views'  => base_path('resources/views/vendor/vaultbox'),
        ], 'Vaultbox_view');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('vaultbox', function ()
        {
            return true;
        });
    }

}
