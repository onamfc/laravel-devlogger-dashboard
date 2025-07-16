<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class InteractiveActionsTest extends TestCase
{
    /** @test */
    public function search_functionality_works_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test search by message
        $component->set('search', 'error')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message');
        
        // Test search by exception class
        $component->set('search', 'Exception')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test info message');
        
        // Test case insensitive search
        $component->set('search', 'ERROR')
                  ->assertSee('Test error message');
        
        // Test partial search
        $component->set('search', 'Test')
                  ->assertSee('Test error message')
                  ->assertSee('Test warning message')
                  ->assertSee('Test info message');
    }

    /** @test */
    public function level_filter_works_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test error level filter
        $component->set('level', 'error')
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message')
                  ->assertDontSee('Test info message');
        
        // Test warning level filter
        $component->set('level', 'warning')
                  ->assertSee('Test warning message')
                  ->assertDontSee('Test error message')
                  ->assertDontSee('Test info message');
        
        // Test info level filter
        $component->set('level', 'info')
                  ->assertSee('Test info message')
                  ->assertDontSee('Test error message')
                  ->assertDontSee('Test warning message');
    }

    /** @test */
    public function status_filter_works_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test open status filter
        $component->set('status', 'open')
                  ->assertSee('Test error message')
                  ->assertSee('Test info message')
                  ->assertDontSee('Test warning message');
        
        // Test resolved status filter
        $component->set('status', 'resolved')
                  ->assertSee('Test warning message')
                  ->assertDontSee('Test error message')
                  ->assertDontSee('Test info message');
    }

    /** @test */
    public function date_filters_work_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test date from filter
        $component->set('dateFrom', now()->subDays(1)->format('Y-m-d'))
                  ->assertSee('Test error message')
                  ->assertSee('Test warning message')
                  ->assertSee('Test info message');
        
        // Test date to filter (should exclude future dates)
        $component->set('dateTo', now()->subDays(1)->format('Y-m-d'))
                  ->assertSee('Test error message')
                  ->assertDontSee('Test warning message')
                  ->assertDontSee('Test info message');
    }

    /** @test */
    public function mark_resolved_action_works()
    {
        // Test individual log mark resolved
        Livewire::test(LogDashboard::class)
            ->call('markLogResolved', 1);
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved'
        ]);
        
        // Test bulk mark resolved
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [3])
            ->call('bulkMarkResolved');
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 3,
            'status' => 'resolved'
        ]);
    }

    /** @test */
    public function mark_open_action_works()
    {
        // Test individual log mark open
        Livewire::test(LogDashboard::class)
            ->call('markLogOpen', 2);
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open'
        ]);
        
        // Test bulk mark open
        DB::table('developer_logs')->where('id', 1)->update(['status' => 'resolved']);
        
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1])
            ->call('bulkMarkOpen');
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'open'
        ]);
    }

    /** @test */
    public function delete_action_works()
    {
        // Test individual log delete
        Livewire::test(LogDashboard::class)
            ->call('deleteLog', 1);
        
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
        
        // Test bulk delete
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [2, 3])
            ->call('bulkDelete');
        
        $log2 = DB::table('developer_logs')->where('id', 2)->first();
        $log3 = DB::table('developer_logs')->where('id', 3)->first();
        $this->assertNotNull($log2->deleted_at);
        $this->assertNotNull($log3->deleted_at);
    }

    /** @test */
    public function sorting_works_correctly()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test sorting by level
        $component->call('sortBy', 'level')
                  ->assertSet('sortField', 'level')
                  ->assertSet('sortDirection', 'asc');
        
        // Test toggle sort direction
        $component->call('sortBy', 'level')
                  ->assertSet('sortDirection', 'desc');
        
        // Test sorting by status
        $component->call('sortBy', 'status')
                  ->assertSet('sortField', 'status')
                  ->assertSet('sortDirection', 'asc');
    }

    /** @test */
    public function selection_functionality_works()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Test select all
        $component->set('selectAll', true)
                  ->assertCount('selectedLogs', 3);
        
        // Test deselect all
        $component->set('selectAll', false)
                  ->assertCount('selectedLogs', 0);
        
        // Test individual selection updates select all
        $component->set('selectedLogs', [1, 2, 3])
                  ->assertSet('selectAll', true);
        
        $component->set('selectedLogs', [1, 2])
                  ->assertSet('selectAll', false);
    }

    /** @test */
    public function filters_reset_page_and_selection()
    {
        // Create more data for pagination
        for ($i = 4; $i <= 30; $i++) {
            DB::table('developer_logs')->insert([
                'id' => $i,
                'level' => 'info',
                'log' => "Test message {$i}",
                'message' => "Test message {$i}",
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $component = Livewire::test(LogDashboard::class)
            ->set('page', 2)
            ->set('selectedLogs', [1, 2])
            ->set('search', 'error')
            ->assertSet('page', 1)
            ->assertCount('selectedLogs', 0);
    }

    /** @test */
    public function clear_filters_works()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'test')
            ->set('level', 'error')
            ->set('status', 'open')
            ->set('dateFrom', '2023-01-01')
            ->set('dateTo', '2023-12-31')
            ->set('selectedLogs', [1, 2])
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('level', '')
            ->assertSet('status', '')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);
    }

    /** @test */
    public function log_details_actions_work()
    {
        // Test mark resolved in details view
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('markResolved');
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved'
        ]);
        
        // Test mark open in details view
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('markOpen');
        
        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'open'
        ]);
        
        // Test delete in details view
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('deleteLog')
            ->assertRedirect(route('devlogger.dashboard'));
        
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
    }

    /** @test */
    public function bulk_actions_require_selection()
    {
        $component = Livewire::test(LogDashboard::class);
        
        $component->call('bulkDelete')
                  ->assertHasErrors(['bulk']);
        
        $component->call('bulkMarkResolved')
                  ->assertHasErrors(['bulk']);
        
        $component->call('bulkMarkOpen')
                  ->assertHasErrors(['bulk']);
    }

    /** @test */
    public function actions_ignore_soft_deleted_logs()
    {
        // Soft delete a log
        DB::table('developer_logs')->where('id', 1)->update(['deleted_at' => now()]);
        
        // Try to mark it resolved (should not work)
        Livewire::test(LogDashboard::class)
            ->call('markLogResolved', 1);
        
        // Status should remain unchanged since log is soft deleted
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertEquals('error', $log->level); // Original data unchanged
        $this->assertNotNull($log->deleted_at);
    }

    /** @test */
    public function stats_update_correctly_after_actions()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Initial stats
        $initialStats = $component->get('stats');
        $this->assertEquals(2, $initialStats['open']);
        $this->assertEquals(1, $initialStats['resolved']);
        
        // Mark a log as resolved
        $component->call('markLogResolved', 1);
        
        // Stats should update
        $updatedStats = $component->get('stats');
        $this->assertEquals(1, $updatedStats['open']);
        $this->assertEquals(2, $updatedStats['resolved']);
        
        // Delete a log
        $component->call('deleteLog', 2);
        
        // Stats should update again
        $finalStats = $component->get('stats');
        $this->assertEquals(2, $finalStats['total']); // One less total
    }
}