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
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('level', '')
            ->assertSet('status', '');
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
    public function it_bulk_deletes_selected_logs()
    {
        Livewire::test(LogDashboard::class)
            ->set('selectedLogs', [1, 2])
            ->call('bulkDelete')
            ->assertCount('selectedLogs', 0)
            ->assertSet('selectAll', false);

        $this->assertDatabaseMissing('developer_logs', [
            'id' => 1,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseMissing('developer_logs', [
            'id' => 2,
            'deleted_at' => null,
        ]);
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
    public function it_shows_error_for_bulk_action_without_selection()
    {
        Livewire::test(LogDashboard::class)
            ->call('bulkDelete')
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
    public function it_resets_page_when_searching()
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
}