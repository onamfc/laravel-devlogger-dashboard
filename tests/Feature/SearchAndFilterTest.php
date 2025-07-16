<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class SearchAndFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Add more diverse test data
        DB::table('developer_logs')->insert([
            [
                'id' => 4,
                'level' => 'critical',
                'log' => 'Database connection failed',
                'message' => 'Database connection failed',
                'exception_class' => 'PDOException',
                'file_path' => '/app/database/connection.php',
                'line_number' => 45,
                'request_url' => '/api/users',
                'request_method' => 'GET',
                'status' => 'open',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 5,
                'level' => 'debug',
                'log' => 'User authentication successful',
                'message' => 'User authentication successful',
                'file_path' => '/app/auth/login.php',
                'line_number' => 123,
                'request_url' => '/login',
                'request_method' => 'POST',
                'status' => 'resolved',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ]
        ]);
    }

    /** @test */
    public function it_searches_by_log_message()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'Database connection')
            ->assertSee('Database connection failed')
            ->assertDontSee('Test error message')
            ->assertDontSee('User authentication');
    }

    /** @test */
    public function it_searches_by_exception_class()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'PDOException')
            ->assertSee('Database connection failed')
            ->assertDontSee('Test error message');
    }

    /** @test */
    public function it_searches_by_file_path()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'database/connection')
            ->assertSee('Database connection failed')
            ->assertDontSee('Test error message');
    }

    /** @test */
    public function it_searches_by_request_url()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', '/api/users')
            ->assertSee('Database connection failed')
            ->assertDontSee('Test error message');
    }

    /** @test */
    public function it_performs_case_insensitive_search()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'DATABASE')
            ->assertSee('Database connection failed');
            
        Livewire::test(LogDashboard::class)
            ->set('search', 'pdoexception')
            ->assertSee('Database connection failed');
    }

    /** @test */
    public function it_performs_partial_search()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'connection')
            ->assertSee('Database connection failed');
            
        Livewire::test(LogDashboard::class)
            ->set('search', 'auth')
            ->assertSee('User authentication successful');
    }

    /** @test */
    public function it_filters_by_all_log_levels()
    {
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
        
        foreach ($levels as $level) {
            $component = Livewire::test(LogDashboard::class)
                ->set('level', $level);
            
            // Should only show logs of that level
            $logs = $component->get('logs');
            foreach ($logs as $log) {
                $this->assertEquals($level, $log->level);
            }
        }
    }

    /** @test */
    public function it_filters_by_status()
    {
        // Test open status
        $component = Livewire::test(LogDashboard::class)
            ->set('status', 'open');
        
        $logs = $component->get('logs');
        foreach ($logs as $log) {
            $this->assertEquals('open', $log->status);
        }
        
        // Test resolved status
        $component = Livewire::test(LogDashboard::class)
            ->set('status', 'resolved');
        
        $logs = $component->get('logs');
        foreach ($logs as $log) {
            $this->assertEquals('resolved', $log->status);
        }
    }

    /** @test */
    public function it_filters_by_date_from()
    {
        Livewire::test(LogDashboard::class)
            ->set('dateFrom', now()->subDay()->format('Y-m-d'))
            ->assertSee('Test error message') // Created 2 hours ago
            ->assertSee('Test warning message') // Created 1 hour ago
            ->assertSee('Test info message') // Created now
            ->assertSee('User authentication successful') // Created 6 hours ago
            ->assertDontSee('Database connection failed'); // Created 2 days ago
    }

    /** @test */
    public function it_filters_by_date_to()
    {
        Livewire::test(LogDashboard::class)
            ->set('dateTo', now()->subDay()->format('Y-m-d'))
            ->assertSee('Database connection failed') // Created 2 days ago
            ->assertDontSee('Test error message') // Created 2 hours ago
            ->assertDontSee('User authentication successful'); // Created 6 hours ago
    }

    /** @test */
    public function it_filters_by_date_range()
    {
        Livewire::test(LogDashboard::class)
            ->set('dateFrom', now()->subDays(3)->format('Y-m-d'))
            ->set('dateTo', now()->subHours(12)->format('Y-m-d'))
            ->assertSee('Database connection failed') // Created 2 days ago
            ->assertDontSee('Test error message') // Created 2 hours ago
            ->assertDontSee('User authentication successful'); // Created 6 hours ago
    }

    /** @test */
    public function it_combines_multiple_filters()
    {
        // Search + Level filter
        Livewire::test(LogDashboard::class)
            ->set('search', 'Test')
            ->set('level', 'error')
            ->assertSee('Test error message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Test info message');
        
        // Level + Status filter
        Livewire::test(LogDashboard::class)
            ->set('level', 'warning')
            ->set('status', 'resolved')
            ->assertSee('Test warning message')
            ->assertDontSee('Test error message');
        
        // Search + Status + Date filter
        Livewire::test(LogDashboard::class)
            ->set('search', 'Test')
            ->set('status', 'open')
            ->set('dateFrom', now()->subHours(3)->format('Y-m-d'))
            ->assertSee('Test error message')
            ->assertSee('Test info message')
            ->assertDontSee('Test warning message')
            ->assertDontSee('Database connection failed');
    }

    /** @test */
    public function it_shows_no_results_message_when_no_matches()
    {
        Livewire::test(LogDashboard::class)
            ->set('search', 'nonexistent search term')
            ->assertSee('No logs found')
            ->assertSee('Try adjusting your search or filter criteria');
    }

    /** @test */
    public function it_resets_pagination_when_filtering()
    {
        // Create enough data to trigger pagination
        for ($i = 6; $i <= 30; $i++) {
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
            ->set('page', 2) // Go to page 2
            ->set('search', 'error') // Apply filter
            ->assertSet('page', 1); // Should reset to page 1
    }

    /** @test */
    public function it_maintains_filters_in_query_string()
    {
        $component = Livewire::test(LogDashboard::class)
            ->set('search', 'test search')
            ->set('level', 'error')
            ->set('status', 'open')
            ->set('dateFrom', '2023-01-01')
            ->set('dateTo', '2023-12-31');
        
        // Check that query string properties are set
        $this->assertEquals('test search', $component->get('search'));
        $this->assertEquals('error', $component->get('level'));
        $this->assertEquals('open', $component->get('status'));
        $this->assertEquals('2023-01-01', $component->get('dateFrom'));
        $this->assertEquals('2023-12-31', $component->get('dateTo'));
    }

    /** @test */
    public function it_clears_all_filters_correctly()
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
            ->assertSet('dateTo', '')
            ->assertSee('Test error message')
            ->assertSee('Test warning message')
            ->assertSee('Test info message')
            ->assertSee('Database connection failed')
            ->assertSee('User authentication successful');
    }

    /** @test */
    public function it_updates_stats_based_on_current_filters()
    {
        // Without filters - should show all logs
        $component = Livewire::test(LogDashboard::class);
        $stats = $component->get('stats');
        $this->assertEquals(5, $stats['total']);
        
        // With level filter - stats should remain global (not filtered)
        $component->set('level', 'error');
        $stats = $component->get('stats');
        $this->assertEquals(5, $stats['total']); // Stats are global, not filtered
        
        // The displayed logs should be filtered though
        $logs = $component->get('logs');
        $this->assertCount(1, $logs); // Only error logs shown
    }

    /** @test */
    public function it_handles_special_characters_in_search()
    {
        // Add log with special characters
        DB::table('developer_logs')->insert([
            'id' => 6,
            'level' => 'error',
            'log' => 'Error with special chars: @#$%^&*()',
            'message' => 'Error with special chars: @#$%^&*()',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        Livewire::test(LogDashboard::class)
            ->set('search', '@#$%')
            ->assertSee('Error with special chars');
    }

    /** @test */
    public function it_handles_empty_and_null_values_in_search()
    {
        // Test empty search
        Livewire::test(LogDashboard::class)
            ->set('search', '')
            ->assertSee('Test error message')
            ->assertSee('Database connection failed');
        
        // Test whitespace search
        Livewire::test(LogDashboard::class)
            ->set('search', '   ')
            ->assertSee('Test error message')
            ->assertSee('Database connection failed');
    }
}