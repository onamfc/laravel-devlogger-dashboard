<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class LogDashboardTest extends TestCase
{
    /** @test */
    public function it_renders_successfully()
    {
        Livewire::test(LogDashboard::class)
            ->assertStatus(200)
            ->assertViewIs('devlogger-dashboard::livewire.log-dashboard');
    }

    /** @test */
    public function it_displays_logs()
    {
        Livewire::test(LogDashboard::class)
            ->assertSee('Test error message')
            ->assertSee('Test warning message')
            ->assertSee('Test info message');
    }

    /** @test */
    public function it_displays_stats()
    {
        Livewire::test(LogDashboard::class)
            ->assertSee('3') // Total logs
            ->assertSee('1') // Today's logs
            ->assertSee('1') // Errors
            ->assertSee('1') // Warnings
            ->assertSee('2') // Open
            ->assertSee('1'); // Resolved
    }

    /** @test */
    public function it_filters_by_search()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'error')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Test info message');
    }

    /** @test */
    public function it_searches_across_multiple_fields()
    {
        // Test searching by exception class
        Livewire::test(LogDashboard::class)
            ->set('search', 'Exception')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message');

        // Test searching by file path
        Livewire::test(LogDashboard::class)
            ->set('search', '/app/test.php')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message');
    }

    /** @test */
    public function it_filters_by_level()
    {
        Livewire::test(LogDashboard::class)
            ->set('level', 'error')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Test info message');
    }

    /** @test */
    public function it_filters_by_status()
    {
        Livewire::test(LogDashboard::class)
            ->set('status', 'resolved')
            ->assertSee('Test warning message')
            ->assertDontSee('Test error message')
            ->assertDontSee('Test info message');
    }

    /** @test */
    public function it_filters_by_date_range()
    {
        Livewire::test(LogDashboard::class)
            ->set('dateFrom', now()->subDays(1)->format('Y-m-d'))
            ->set('dateTo', now()->subHours(3)->format('Y-m-d'))
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Test info message');
    }

    /** @test */
    public function it_combines_multiple_filters()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'Test')
            ->set('level', 'error')
            ->set('status', 'open')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Test info message');
    }

    /** @test */
    public function it_sorts_by_different_fields()
    {
        Livewire::test(LogDashboard::class)
            ->call('sortBy', 'level')
            ->assertSet('sortField', 'level')
            ->assertSet('sortDirection', 'asc');
    }

    /** @test */
    public function it_toggles_sort_direction()
    {
        Livewire::test(LogDashboard::class)
            ->call('sortBy', 'created_at')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc');
    }

    /** @test */
    public function it_clears_filters()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'test')
            ->set('level', 'error')
            ->set('status', 'open')
            ->set('dateFrom', '2023-01-01')
            ->set('dateTo', '2023-12-31')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('level', '')
            ->assertSet('status', '')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '');
    }

    /** @test */
    public function it_toggles_filters_visibility()
    {
        Livewire::test(LogDashboard::class)
            ->assertSet('showFilters', false)
            ->call('toggleFilters')
            ->assertSet('showFilters', true)
            ->call('toggleFilters')
            ->assertSet('showFilters', false);
    }

    /** @test */
    public function it_selects_all_logs()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectAll', true)
            ->assertCount('selectedLogs', 3);
    }

    /** @test */
    public function it_deselects_all_logs()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectAll', true)
            ->set('selectAll', false)
            ->assertCount('selectedLogs', 0);
    }

    /** @test */
    public function it_updates_select_all_when_individual_logs_selected()
    {
        $component = Livewire::test(LogDashboard::class);
        
        // Select all logs individually
        $component->set('selectedLogs', [1, 2, 3])
                  ->assertSet('selectAll', true);
        
        // Deselect one log
        $component->set('selectedLogs', [1, 2])
                  ->assertSet('selectAll', false);
    }

    /** @test */
    public function it_bulk_deletes_selected_logs()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1, 2])
            ->call('bulkDelete')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);

        $this->assertDatabaseHas('developer_logs', ['id' => 1]);
        $this->assertDatabaseHas('developer_logs', ['id' => 2]);
        
        // Check soft delete
        $log1 = DB::table('developer_logs')->where('id', 1)->first();
        $log2 = DB::table('developer_logs')->where('id', 2)->first();
        $this->assertNotNull($log1->deleted_at);
        $this->assertNotNull($log2->deleted_at);
    }

    /** @test */
    public function it_bulk_marks_resolved()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1, 3])
            ->call('bulkMarkResolved')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved',
        ]);
        $this->assertDatabaseHas('developer_logs', [
            'id' => 3,
            'status' => 'resolved',
        ]);
    }

    /** @test */
    public function it_bulk_marks_open()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [2])
            ->call('bulkMarkOpen')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_marks_individual_log_resolved()
    {
        Livewire::test(LogDashboard::class)
            ->call('markLogResolved', 1);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved',
        ]);
    }

    /** @test */
    public function it_marks_individual_log_open()
    {
        Livewire::test(LogDashboard::class)
            ->call('markLogOpen', 2);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_deletes_individual_log()
    {
        Livewire::test(LogDashboard::class)
            ->call('deleteLog', 1);

        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
    }

    /** @test */
    public function it_shows_error_for_bulk_action_without_selection()
    {
        Livewire::test(LogDashboard::class)
            ->call('bulkDelete')
            ->assertHasErrors(['bulk']);

        Livewire::test(LogDashboard::class)
            ->call('bulkMarkResolved')
            ->assertHasErrors(['bulk']);

        Livewire::test(LogDashboard::class)
            ->call('bulkMarkOpen')
            ->assertHasErrors(['bulk']);
    }

    /** @test */
    public function it_excludes_soft_deleted_logs()
    {
        DB::table('developer_logs')->where('id', 1)->update(['deleted_at' => now()]);

        Livewire::test(LogDashboard::class)
            ->assertDontSee('Test error message')
            ->assertSee('Test warning message')
            ->assertSee('Test info message');
    }

    /** @test */
    public function it_updates_stats_correctly()
    {
        // Add more test data
        DB::table('developer_logs')->insert([
            [
                'id' => 4,
                'level' => 'critical',
                'log' => 'Critical error',
                'message' => 'Critical error',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'level' => 'alert',
                'log' => 'Alert message',
                'message' => 'Alert message',
                'status' => 'resolved',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]
        ]);

        $component = Livewire::test(LogDashboard::class);
        $stats = $component->get('stats');

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(2, $stats['today']); // 3 original + 1 new today
        $this->assertEquals(3, $stats['errors']); // error, critical, alert
        $this->assertEquals(1, $stats['warnings']);
        $this->assertEquals(3, $stats['open']);
        $this->assertEquals(2, $stats['resolved']);
    }

    /** @test */
    public function it_paginates_results()
    {
        // Create more test data
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

        Livewire::test(LogDashboard::class)
            ->assertSee('Test message 4')
            ->assertDontSee('Test message 30'); // Should be on next page
    }

    /** @test */
    public function it_resets_page_when_filtering()
    {
        // Create more test data to trigger pagination
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

        Livewire::test(LogDashboard::class)
            ->set('page', 2)
            ->set('search', 'error')
            ->assertSet('page', 1);
    }

    /** @test */
    public function it_resets_selection_when_filtering()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1, 2])
            ->set('search', 'error')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);
    }

    /** @test */
    public function it_removes_deleted_log_from_selection()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1, 2, 3])
            ->call('deleteLog', 2)
            ->assertCount('selectedLogs', 2)
            ->assertNotContains('selectedLogs', 2);
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'nonexistent')
            ->assertSee('No logs found')
            ->assertSee('Try adjusting your search or filter criteria');
    }

    /** @test */
    public function it_displays_flash_messages()
    {
        Livewire::test(LogDashboard::class)
            ->call('clearFilters')
            ->assertSessionHas('success', 'Filters cleared successfully.');
    }
}