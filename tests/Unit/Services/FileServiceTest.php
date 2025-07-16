<?php

namespace Onamfc\DevLoggerDashboard\Tests\Unit\Services;

use Onamfc\DevLoggerDashboard\Services\FileService;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class FileServiceTest extends TestCase
{
    protected FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileService = new FileService();
    }

    /** @test */
    public function it_returns_null_when_preview_is_disabled()
    {
        config(['devlogger-dashboard.file_path.show_preview' => false]);

        $result = $this->fileService->getFilePreview('/some/file.php', 1, 5);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_non_existent_file()
    {
        config(['devlogger-dashboard.file_path.show_preview' => true]);

        $result = $this->fileService->getFilePreview('/non/existent/file.php', 1, 5);

        $this->assertNull($result);
    }

    /** @test */
    public function it_generates_file_preview_for_existing_file()
    {
        config(['devlogger-dashboard.file_path.show_preview' => true]);
        
        // Create a temporary test file
        $testFile = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($testFile, "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\nLine 6\nLine 7");

        $result = $this->fileService->getFilePreview($testFile, 3, 2);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('total_lines', $result);
        $this->assertArrayHasKey('target_line', $result);
        $this->assertArrayHasKey('file_path', $result);

        $this->assertEquals(3, $result['target_line']);
        $this->assertEquals(7, $result['total_lines']);
        $this->assertCount(5, $result['lines']); // 2 before + target + 2 after

        // Check that target line is marked correctly
        $targetLine = collect($result['lines'])->firstWhere('is_target', true);
        $this->assertEquals(3, $targetLine['number']);
        $this->assertEquals('Line 3', $targetLine['content']);

        // Clean up
        unlink($testFile);
    }

    /** @test */
    public function it_handles_line_numbers_at_file_boundaries()
    {
        config(['devlogger-dashboard.file_path.show_preview' => true]);
        
        // Create a temporary test file with only 3 lines
        $testFile = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($testFile, "Line 1\nLine 2\nLine 3");

        // Test first line
        $result = $this->fileService->getFilePreview($testFile, 1, 2);
        $this->assertEquals(1, $result['lines'][0]['number']);
        $this->assertEquals(3, $result['lines'][2]['number']);

        // Test last line
        $result = $this->fileService->getFilePreview($testFile, 3, 2);
        $this->assertEquals(1, $result['lines'][0]['number']);
        $this->assertEquals(3, $result['lines'][2]['number']);

        // Clean up
        unlink($testFile);
    }

    /** @test */
    public function it_converts_relative_paths_to_absolute()
    {
        config([
            'devlogger-dashboard.file_path.show_preview' => true,
            'devlogger-dashboard.file_path.base_path' => '/app'
        ]);

        // Create a temporary test file
        $testFile = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($testFile, "Line 1\nLine 2\nLine 3");

        // Test with absolute path
        $result = $this->fileService->getFilePreview($testFile, 1, 1);
        $this->assertEquals($testFile, $result['file_path']);

        // Clean up
        unlink($testFile);
    }
}