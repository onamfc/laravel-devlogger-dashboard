<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_livewire_components()
    {
        $this->assertTrue(Livewire::isLivewireComponent(LogDashboard::class));
        $this->assertTrue(Livewire::isLivewireComponent(LogDetails::class));
    }

    /** @test */
    public function it_registers_middleware()
    {
        $middleware = $this->app['router']->getMiddleware();
        
        $this->assertArrayHasKey('devlogger.dashboard', $middleware);
    }

    /** @test */
    public function it_loads_views()
    {
        $this->assertTrue(view()->exists('devlogger-dashboard::dashboard'));
        $this->assertTrue(view()->exists('devlogger-dashboard::db-logs.show'));
        $this->assertTrue(view()->exists('devlogger-dashboard::livewire.log-dashboard'));
        $this->assertTrue(view()->exists('devlogger-dashboard::livewire.log-details'));
    }

    /** @test */
    public function it_merges_config()
    {
        $this->assertNotNull(config('devlogger-dashboard.route_prefix'));
        $this->assertNotNull(config('devlogger-dashboard.middleware'));
        $this->assertNotNull(config('devlogger-dashboard.allowed_environments'));
    }

    /** @test */
    public function it_registers_routes()
    {
        $routes = collect($this->app['router']->getRoutes())->map(function ($route) {
            return $route->uri();
        });

        $this->assertTrue($routes->contains('devlogger'));
        $this->assertTrue($routes->contains('devlogger/db-logs/{log}'));
        $this->assertTrue($routes->contains('devlogger/db-logs/bulk-action'));
        $this->assertTrue($routes->contains('devlogger/db-logs/{log}/file'));
    }

    /** @test */
    public function it_respects_custom_route_prefix()
    {
        config(['devlogger-dashboard.route_prefix' => 'custom-logs']);
        
        // Re-register routes with new config
        $this->app->forgetInstance('router');
        $this->refreshApplication();
        
        $routes = collect($this->app['router']->getRoutes())->map(function ($route) {
            return $route->uri();
        });

        $this->assertTrue($routes->contains('custom-logs'));
    }
}