<?php

namespace Insowe\AdminTemplate;

use Illuminate\Support\ServiceProvider;

class AdminTemplateServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'insowe');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'insowe');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/admintemplate.php', 'admintemplate');

        // Register the service the package provides.
        $this->app->singleton('admintemplate', function ($app) {
            return new AdminTemplate;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['admintemplate'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/admintemplate.php' => config_path('admintemplate.php'),
        ], 'admintemplate.config');
        
        // Publishing the database migration file.
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'admintemplate.migrations');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/insowe'),
        ], 'admintemplate.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/insowe'),
        ], 'admintemplate.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
