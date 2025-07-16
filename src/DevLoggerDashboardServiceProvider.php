<?php

namespace Onamfc\DevLoggerDashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Http\Middleware\DevLoggerDashboardMiddleware;

class DevLoggerDashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/devlogger-dashboard.php',
            'devlogger-dashboard'
        );
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'devlogger-dashboard');
        
        $this->publishes([
            __DIR__.'/../config/devlogger-dashboard.php' => config_path('devlogger-dashboard.php'),
        ], 'devlogger-dashboard-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/devlogger-dashboard'),
        ], 'devlogger-dashboard-views');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/devlogger-dashboard'),
        ], 'devlogger-dashboard-assets');

        // Register Livewire components - ensure they're registered correctly
        if (class_exists(\Livewire\Livewire::class)) {
            Livewire::component('devlogger-dashboard', LogDashboard::class);
            Livewire::component('devlogger-log-details', LogDetails::class);
        }

        // Register middleware
        $this->app['router']->aliasMiddleware('devlogger.dashboard', DevLoggerDashboardMiddleware::class);

        // Register routes
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('devlogger-dashboard.route_prefix', 'devlogger'),
            'middleware' => config('devlogger-dashboard.middleware', ['web', 'devlogger.dashboard']),
            'namespace' => 'Onamfc\DevLoggerDashboard\Http\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}