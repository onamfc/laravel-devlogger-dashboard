<?php

namespace Onamfc\DevLoggerDashboard\Tests\Unit\Services;

use Onamfc\DevLoggerDashboard\Services\IdeService;
use Onamfc\DevLoggerDashboard\Tests\TestCase;

class IdeServiceTest extends TestCase
{
    protected IdeService $ideService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ideService = new IdeService();
    }

    /** @test */
    public function it_generates_vscode_url()
    {
        config([
            'devlogger-dashboard.ide.default' => 'vscode',
            'devlogger-dashboard.ide.handlers' => [
                'vscode' => 'vscode://file/{file}:{line}'
            ]
        ]);

        $url = $this->ideService->generateIdeUrl('/app/test.php', 10);

        $this->assertEquals('vscode://file//app/test.php:10', $url);
    }

    /** @test */
    public function it_generates_phpstorm_url()
    {
        config([
            'devlogger-dashboard.ide.default' => 'phpstorm',
            'devlogger-dashboard.ide.handlers' => [
                'phpstorm' => 'phpstorm://open?file={file}&line={line}'
            ]
        ]);

        $url = $this->ideService->generateIdeUrl('/app/test.php', 15);

        $this->assertEquals('phpstorm://open?file=/app/test.php&line=15', $url);
    }

    /** @test */
    public function it_throws_exception_for_unknown_ide()
    {
        config([
            'devlogger-dashboard.ide.default' => 'unknown',
            'devlogger-dashboard.ide.handlers' => []
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("IDE handler for 'unknown' not found.");

        $this->ideService->generateIdeUrl('/app/test.php', 10);
    }

    /** @test */
    public function it_returns_supported_ides()
    {
        $handlers = [
            'vscode' => 'vscode://file/{file}:{line}',
            'phpstorm' => 'phpstorm://open?file={file}&line={line}',
        ];

        config(['devlogger-dashboard.ide.handlers' => $handlers]);

        $result = $this->ideService->getSupportedIdes();

        $this->assertEquals($handlers, $result);
    }

    /** @test */
    public function it_handles_relative_paths()
    {
        config([
            'devlogger-dashboard.ide.default' => 'vscode',
            'devlogger-dashboard.ide.handlers' => [
                'vscode' => 'vscode://file/{file}:{line}'
            ],
            'devlogger-dashboard.file_path.base_path' => '/app'
        ]);

        $url = $this->ideService->generateIdeUrl('src/test.php', 10);

        $this->assertEquals('vscode://file//app/src/test.php:10', $url);
    }

    /** @test */
    public function it_defaults_line_number_to_1()
    {
        config([
            'devlogger-dashboard.ide.default' => 'vscode',
            'devlogger-dashboard.ide.handlers' => [
                'vscode' => 'vscode://file/{file}:{line}'
            ]
        ]);

        $url = $this->ideService->generateIdeUrl('/app/test.php');

        $this->assertEquals('vscode://file//app/test.php:1', $url);
    }
}