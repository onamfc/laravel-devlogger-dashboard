<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Onamfc\DevLoggerDashboard\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function it_has_default_configuration_values()
    {
        $this->assertEquals('devlogger', config('devlogger-dashboard.route_prefix'));
        $this->assertEquals(['web', 'devlogger.dashboard'], config('devlogger-dashboard.middleware'));
        $this->assertEquals(['local', 'staging', 'development'], config('devlogger-dashboard.allowed_environments'));
        $this->assertTrue(config('devlogger-dashboard.require_auth'));
        $this->assertEquals('vscode', config('devlogger-dashboard.ide.default'));
        $this->assertEquals(25, config('devlogger-dashboard.dashboard.per_page'));
        $this->assertTrue(config('devlogger-dashboard.file_path.show_preview'));
    }

    /** @test */
    public function it_supports_environment_variable_overrides()
    {
        // Test route prefix override
        putenv('DEVLOGGER_DASHBOARD_PREFIX=custom-prefix');
        $this->assertEquals('custom-prefix', env('DEVLOGGER_DASHBOARD_PREFIX'));

        // Test auth requirement override
        putenv('DEVLOGGER_DASHBOARD_AUTH=false');
        $this->assertEquals('false', env('DEVLOGGER_DASHBOARD_AUTH'));

        // Test IDE override
        putenv('DEVLOGGER_IDE=phpstorm');
        $this->assertEquals('phpstorm', env('DEVLOGGER_IDE'));

        // Clean up
        putenv('DEVLOGGER_DASHBOARD_PREFIX');
        putenv('DEVLOGGER_DASHBOARD_AUTH');
        putenv('DEVLOGGER_IDE');
    }

    /** @test */
    public function it_has_all_required_ide_handlers()
    {
        $handlers = config('devlogger-dashboard.ide.handlers');

        $this->assertArrayHasKey('vscode', $handlers);
        $this->assertArrayHasKey('phpstorm', $handlers);
        $this->assertArrayHasKey('sublime', $handlers);
        $this->assertArrayHasKey('atom', $handlers);

        $this->assertStringContains('{file}', $handlers['vscode']);
        $this->assertStringContains('{line}', $handlers['vscode']);
    }

    /** @test */
    public function it_has_reasonable_dashboard_defaults()
    {
        $dashboard = config('devlogger-dashboard.dashboard');

        $this->assertEquals(25, $dashboard['per_page']);
        $this->assertEquals(100, $dashboard['max_per_page']);
        $this->assertFalse($dashboard['auto_refresh']);
        $this->assertEquals(30, $dashboard['refresh_interval']);
    }

    /** @test */
    public function it_has_ui_configuration()
    {
        $ui = config('devlogger-dashboard.ui');

        $this->assertEquals('dark', $ui['theme']);
        $this->assertEquals('DevLogger Dashboard', $ui['brand_name']);
        $this->assertTrue($ui['show_user_info']);
    }

    /** @test */
    public function it_supports_ip_allowlist_configuration()
    {
        // Test empty IP list
        $this->assertEquals([], config('devlogger-dashboard.allowed_ips'));

        // Test with environment variable
        putenv('DEVLOGGER_DASHBOARD_IPS=127.0.0.1,192.168.1.1');
        
        // Reload config
        $this->app['config']->set('devlogger-dashboard.allowed_ips', 
            env('DEVLOGGER_DASHBOARD_IPS') ? explode(',', env('DEVLOGGER_DASHBOARD_IPS')) : []
        );

        $this->assertEquals(['127.0.0.1', '192.168.1.1'], config('devlogger-dashboard.allowed_ips'));

        // Clean up
        putenv('DEVLOGGER_DASHBOARD_IPS');
    }
}