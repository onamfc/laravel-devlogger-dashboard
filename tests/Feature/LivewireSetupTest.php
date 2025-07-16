<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class LivewireSetupTest extends TestCase
{
    /** @test */
    public function livewire_is_properly_configured()
    {
        $this->assertTrue(class_exists(\Livewire\Livewire::class), 'Livewire class should exist');
    }

    /** @test */
    public function livewire_components_are_registered()
    {
        $this->assertTrue(Livewire::isLivewireComponent(LogDashboard::class));
        $this->assertTrue(Livewire::isLivewireComponent(LogDetails::class));
    }

    /** @test */
    public function livewire_components_can_be_resolved()
    {
        $dashboard = Livewire::test(LogDashboard::class);
        $this->assertNotNull($dashboard);

        $details = Livewire::test(LogDetails::class, ['logId' => 1]);
        $this->assertNotNull($details);
    }

    /** @test */
    public function livewire_scripts_are_included_in_layout()
    {
        $response = $this->get('/devlogger');
        
        $response->assertStatus(200);
        $response->assertSee('@livewireScripts', false);
        $response->assertSee('@livewireStyles', false);
    }

    /** @test */
    public function dashboard_component_renders_with_livewire()
    {
        $response = $this->get('/devlogger');
        
        $response->assertStatus(200);
        $response->assertSeeLivewire('devlogger-dashboard');
    }

    /** @test */
    public function log_details_component_renders_with_livewire()
    {
        $response = $this->get('/devlogger/db-logs/1');
        
        $response->assertStatus(200);
        $response->assertSeeLivewire('devlogger-log-details');
    }

    /** @test */
    public function livewire_component_methods_are_callable()
    {
        // Test dashboard component methods
        $dashboard = Livewire::test(LogDashboard::class);
        
        // These should not throw exceptions
        $dashboard->call('clearFilters');
        $dashboard->call('toggleFilters');
        $dashboard->set('search', 'test');
        
        // Test details component methods
        $details = Livewire::test(LogDetails::class, ['logId' => 1]);
        
        // These should not throw exceptions
        $details->call('toggleStackTrace');
        $details->call('toggleContext');
    }

    /** @test */
    public function livewire_properties_are_reactive()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test that properties can be set and retrieved
        $component->set('search', 'test search');
        $component->assertSet('search', 'test search');
        
        $component->set('level', 'error');
        $component->assertSet('level', 'error');
        
        $component->set('status', 'open');
        $component->assertSet('status', 'open');
    }

    /** @test */
    public function livewire_events_can_be_dispatched()
    {
        $component = Livewire::test(LogDetails::class, ['logId' => 1]);
        
        // Test event dispatching
        $component->call('copyFilePath');
        $component->assertDispatched('copy-to-clipboard');
    }
}