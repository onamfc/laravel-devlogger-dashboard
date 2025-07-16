<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class LogControllerTest extends TestCase
{
    /** @test */
    public function it_shows_log_details()
    {
        $response = $this->get('/devlogger/db-logs/1');

        $response->assertStatus(200);
        $response->assertViewIs('devlogger-dashboard::db-logs.show');
        $response->assertViewHas('log');
        $response->assertSeeLivewire('devlogger-log-details');
    }

    /** @test */
    public function it_returns_404_for_non_existent_log()
    {
        $response = $this->get('/devlogger/db-logs/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_soft_deleted_log()
    {
        DB::table('developer_logs')->where('id', 1)->update(['deleted_at' => now()]);

        $response = $this->get('/devlogger/db-logs/1');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_soft_deletes_log()
    {
        $response = $this->delete('/devlogger/db-logs/1');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
        ]);
        $this->assertDatabaseMissing('developer_logs', [
            'id' => 1,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_log()
    {
        $response = $this->delete('/devlogger/db-logs/999');

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function it_performs_bulk_delete_action()
    {
        $response = $this->post('/devlogger/db-logs/bulk-action', [
            'action' => 'delete',
            'ids' => [1, 2]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 2]);

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
    public function it_performs_bulk_mark_resolved_action()
    {
        $response = $this->post('/devlogger/db-logs/bulk-action', [
            'action' => 'mark_resolved',
            'ids' => [1, 3]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 2]);

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
    public function it_performs_bulk_mark_open_action()
    {
        $response = $this->post('/devlogger/db-logs/bulk-action', [
            'action' => 'mark_open',
            'ids' => [2]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 1]);

        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_validates_bulk_action_request()
    {
        $response = $this->post('/devlogger/db-logs/bulk-action', [
            'action' => 'invalid_action',
            'ids' => [1]
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_generates_ide_url_for_file()
    {
        config([
            'devlogger-dashboard.ide.default' => 'vscode',
            'devlogger-dashboard.ide.handlers' => [
                'vscode' => 'vscode://file/{file}:{line}'
            ]
        ]);

        $response = $this->get('/devlogger/db-logs/1/file');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ide_url' => 'vscode://file//app/test.php:10',
            'file_path' => '/app/test.php',
            'line_number' => 10
        ]);
    }

    /** @test */
    public function it_returns_error_for_log_without_file_path()
    {
        $response = $this->get('/devlogger/db-logs/2/file');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'File path not found.'
        ]);
    }
}