<?php

namespace Onamfc\DevLoggerDashboard\Tests\Feature\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Onamfc\DevLoggerDashboard\Http\Livewire\LogDetails;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class LogDetailsTest extends TestCase
{
    /** @test */
    public function it_renders_successfully()
    {
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->assertStatus(200)
            ->assertViewIs('devlogger-dashboard::livewire.log-details');
    }

    /** @test */
    public function it_displays_log_details()
    {
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->assertSee('Test error message')
            ->assertSee('Exception')
            ->assertSee('/app/test.php')
            ->assertSee('10');
    }

    /** @test */
    public function it_marks_log_as_resolved()
    {
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('markResolved');

        $this->assertDatabaseHas('developer_logs', [
            'id' => 1,
            'status' => 'resolved',
        ]);
    }

    /** @test */
    public function it_marks_log_as_open()
    {
        Livewire::test(LogDetails::class, ['logId' => 2])
            ->call('markOpen');

        $this->assertDatabaseHas('developer_logs', [
            'id' => 2,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_deletes_log()
    {
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('deleteLog')
            ->assertRedirect(route('devlogger.dashboard'));

        // Check soft delete
        $log = DB::table('developer_logs')->where('id', 1)->first();
        $this->assertNotNull($log->deleted_at);
    }

    /** @test */
    public function it_toggles_stack_trace_visibility()
    {
        // Add stack trace to test log
        DB::table('developer_logs')->where('id', 1)->update([
            'stack_trace' => 'Stack trace content'
        ]);

        Livewire::test(LogDetails::class, ['logId' => 1])
            ->assertSet('showStackTrace', false)
            ->call('toggleStackTrace')
            ->assertSet('showStackTrace', true)
            ->call('toggleStackTrace')
            ->assertSet('showStackTrace', false);
    }

    /** @test */
    public function it_toggles_context_visibility()
    {
        // Add context to test log
        DB::table('developer_logs')->where('id', 1)->update([
            'context' => json_encode(['key' => 'value'])
        ]);

        Livewire::test(LogDetails::class, ['logId' => 1])
            ->assertSet('showContext', false)
            ->call('toggleContext')
            ->assertSet('showContext', true)
            ->call('toggleContext')
            ->assertSet('showContext', false);
    }

    /** @test */
    public function it_dispatches_copy_file_path_event()
    {
        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('copyFilePath')
            ->assertDispatched('copy-to-clipboard', ['text' => '/app/test.php']);
    }

    /** @test */
    public function it_dispatches_open_ide_event()
    {
        config([
            'devlogger-dashboard.ide.default' => 'vscode',
            'devlogger-dashboard.ide.handlers' => [
                'vscode' => 'vscode://file/{file}:{line}'
            ]
        ]);

        Livewire::test(LogDetails::class, ['logId' => 1])
            ->call('openInIde')
            ->assertDispatched('open-ide-url', ['url' => 'vscode://file//app/test.php:10']);
    }

    /** @test */
    public function it_handles_log_without_file_path()
    {
        Livewire::test(LogDetails::class, ['logId' => 2])
            ->call('openInIde')
            ->assertHasErrors(['ide']);
    }

    /** @test */
    public function it_loads_file_preview_when_available()
    {
        // Create a temporary test file
        $testFile = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($testFile, "Line 1\nLine 2\nLine 3\nLine 4\nLine 5");

        // Update log with test file path
        DB::table('developer_logs')->where('id', 1)->update([
            'file_path' => $testFile,
            'line_number' => 3
        ]);

        config(['devlogger-dashboard.file_path.show_preview' => true]);

        $component = Livewire::test(LogDetails::class, ['logId' => 1]);
        
        $this->assertNotNull($component->get('filePreview'));
        $this->assertEquals(3, $component->get('filePreview')['target_line']);

        // Clean up
        unlink($testFile);
    }

    /** @test */
    public function it_handles_missing_log()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::test(LogDetails::class, ['logId' => 999]);
    }

    /** @test */
    public function it_handles_soft_deleted_log()
    {
        DB::table('developer_logs')->where('id', 1)->update(['deleted_at' => now()]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::test(LogDetails::class, ['logId' => 1]);
    }

    /** @test */
    public function it_decodes_json_fields_properly()
    {
        DB::table('developer_logs')->where('id', 1)->update([
            'context' => json_encode(['user_id' => 123, 'action' => 'test']),
            'tags' => json_encode(['error', 'critical'])
        ]);

        $component = Livewire::test(LogDetails::class, ['logId' => 1]);
        
        $log = $component->get('log');
        $this->assertIsArray($log->context);
        $this->assertEquals(123, $log->context['user_id']);
        $this->assertIsArray($log->tags);
        $this->assertContains('error', $log->tags);
    }
}