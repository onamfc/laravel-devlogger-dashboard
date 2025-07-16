<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class FunctionalityTest extends TestCase
{
    /** @test */
    public function search_functionality_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Initial state - should see all logs
        $component->assertSee('Test error message')
                  ->assertSee('Test warning message')
                  ->assertSee('Test info message');
        
        // Search for 'error' - should only see error log
        $component->set('search', 'error')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message')
                  ->assertDontSee('Test info message');
        
        // Clear search - should see all logs again
        $component->set('search', '')
                  ->assertSee('Test error message')
                  ->assertSee('Test warning message')
                  ->assertSee('Test info message');
    }

    /** @test */
    public function level_filter_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Filter by error level
        $component->set('level', 'error')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message')
                  ->assertDontSee('Test info message');
        
        // Filter by warning level
        $component->set('level', 'warning')
                  ->assertSee('Test warning message')
                  ->assertDontSee('Test error message')
                  ->assertDontSee('Test info message');
        
        // Clear filter
        $component->set('level', '')
                  ->assertSee('Test error message')
                  ->assertSee('Test warning message')
                  ->assertSee('Test info message');
    }

    /** @test */
    public function status_filter_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Filter by open status
        $component->set('status', 'open')
                  ->assertSee('Test error message')
                  ->assertSee('Test info message')
                  ->assertDontSee('Test warning message');
        
        // Filter by resolved status
        $component->set('status', 'resolved')
                  ->assertSee('Test warning message')
                  ->assertDontSee('Test error message')
                  ->assertDontSee('Test info message');
    }

    /** @test */
    public function mark_resolved_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Mark log as resolved
        $component->call('markLogResolved', 1);
        
        // Verify database was updated
        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved'
        ]);
        
        // Verify UI reflects the change
        $component->set('status', 'resolved')
                  ->assertSee('Test error message');
    }

    /** @test */
    public function mark_open_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Mark resolved log as open
        $component->call('markLogOpen', 2);
        
        // Verify database was updated
        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open'
        ]);
        
        // Verify UI reflects the change
        $component->set('status', 'open')
                  ->assertSee('Test warning message');
    }

    /** @test */
    public function delete_log_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Initially should see the log
        $component->assertSee('Test error message');
        
        // Delete the log
        $component->call('deleteLog', 1);
        
        // Verify soft delete in database
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
        
        // Verify UI no longer shows the log
        $component = Livewire::test(LogDashboard::class);
        $component->assertDontSee('Test error message');
    }

    /** @test */
    public function bulk_actions_work_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Select multiple logs
        $component->set('selectedLogs', [1, 3]);
        
        // Bulk mark as resolved
        $component->call('bulkMarkResolved');
        
        // Verify database updates
        $this->assertDatabaseHas('developer_logs', ['id' => 1, 'status' => 'resolved']);
        $this->assertDatabaseHas('developer_logs', ['id' => 3, 'status' => 'resolved']);
        
        // Verify selection is cleared
        $component->assertSet('selectedLogs', []);
    }

    /** @test */
    public function bulk_delete_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Select logs for deletion
        $component->set('selectedLogs', [1, 2]);
        
        // Bulk delete
        $component->call('bulkDelete');
        
        // Verify soft delete in database
        $log1 = DB::table('developer_logs')->where('id', 1)->first();
        $log2 = DB::table('developer_logs')->where('id', 2)->first();
        $this->assertNotNull($log1->deleted_at);
        $this->assertNotNull($log2->deleted_at);
        
        // Verify logs no longer appear in UI
        $component = Livewire::test(LogDashboard::class);
        $component->assertDontSee('Test error message')
                  ->assertDontSee('Test warning message');
    }

    /** @test */
    public function selection_system_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test select all
        $component->set('selectAll', true);
        $component->assertCount('selectedLogs', 3);
        
        // Test deselect all
        $component->set('selectAll', false);
        $component->assertCount('selectedLogs', 0);
        
        // Test individual selection
        $component->set('selectedLogs', [1, 2]);
        $component->assertCount('selectedLogs', 2);
        
        // Test that selecting all items updates selectAll
        $component->set('selectedLogs', [1, 2, 3]);
        $component->assertSet('selectAll', true);
    }

    /** @test */
    public function sorting_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test sorting by level
        $component->call('sortBy', 'level');
        $component->assertSet('sortField', 'level');
        $component->assertSet('sortDirection', 'asc');
        
        // Test toggle sort direction
        $component->call('sortBy', 'level');
        $component->assertSet('sortDirection', 'desc');
        
        // Test sorting by different field
        $component->call('sortBy', 'status');
        $component->assertSet('sortField', 'status');
        $component->assertSet('sortDirection', 'asc');
    }

    /** @test */
    public function clear_filters_works_end_to_end()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Set multiple filters
        $component->set('search', 'test')
                  ->set('level', 'error')
                  ->set('status', 'open')
                  ->set('dateFrom', '2023-01-01')
                  ->set('dateTo', '2023-12-31');
        
        // Clear all filters
        $component->call('clearFilters');
        
        // Verify all filters are cleared
        $component->assertSet('search', '')
                  ->assertSet('level', '')
                  ->assertSet('status', '')
                  ->assertSet('dateFrom', '')
                  ->assertSet('dateTo', '');
    }

    /** @test */
    public function log_details_actions_work_end_to_end()
    {
        $component = Livewire::test(LogDetails::class, ['logId' => 1]);
        
        // Test mark resolved
        $component->call('markResolved');
        $this->assertDatabaseHas('developer_logs', ['id' => 1, 'status' => 'resolved']);
        
        // Test mark open
        $component->call('markOpen');
        $this->assertDatabaseHas('developer_logs', ['id' => 1, 'status' => 'open']);
        
        // Test delete
        $component->call('deleteLog');
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
    }

    /** @test */
    public function stats_update_correctly_after_actions()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Get initial stats
        $initialStats = $component->get('stats');
        $this->assertEquals(2, $initialStats['open']);
        $this->assertEquals(1, $initialStats['resolved']);
        
        // Mark a log as resolved
        $component->call('markLogResolved', 1);
        
        // Get updated stats
        $updatedStats = $component->get('stats');
        $this->assertEquals(1, $updatedStats['open']);
        $this->assertEquals(2, $updatedStats['resolved']);
    }

    /** @test */
    public function combined_filters_work_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Combine search and level filter
        $component->set('search', 'Test')
                  ->set('level', 'error')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message')
                  ->assertDontSee('Test info message');
        
        // Add status filter
        $component->set('status', 'open')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message');
    }
}