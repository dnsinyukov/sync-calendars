<?php

namespace Dnsinyukov\SyncCalendars;

use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/services.php',
            'services'
        );

        $this->app->singleton(CalendarManager::class, function ($app) {
            return new CalendarManager($app);
        });

        $this->app->alias(CalendarManager::class, 'calendar');
    }
}
