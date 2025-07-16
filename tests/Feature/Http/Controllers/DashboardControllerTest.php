<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature\Http\Controllers;

use Onamfc\DevLoggerDashboard\Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    /** @test */
    public function it_displays_dashboard_page()
    {
        $response = $this->get('/devlogger');

        $response->assertStatus(200);
        $response->assertViewIs('devlogger-dashboard::dashboard');
    }

    /** @test */
    public function it_loads_livewire_component()
    {
        $response = $this->get('/devlogger');

        $response->assertStatus(200);
        $response->assertSeeLivewire('devlogger-dashboard');
    }

    /** @test */
    public function it_respects_custom_route_prefix()
    {
        config(['devlogger-dashboard.route_prefix' => 'custom-logs']);

        $response = $this->get('/custom-logs');

        $response->assertStatus(200);
        $response->assertViewIs('devlogger-dashboard::dashboard');
    }
}