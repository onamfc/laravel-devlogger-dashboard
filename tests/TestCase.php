<?php

namespace Onamfc\DevLoggerDashboard\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Onamfc\DevLoggerDashboard\DevLoggerDashboardServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDeveloperLogsTable();
        $this->seedTestData();
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            DevLoggerDashboardServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('devlogger-dashboard.allowed_environments', ['testing']);
        config()->set('devlogger-dashboard.require_auth', false);
    }

    protected function createDeveloperLogsTable()
    {
        Schema::create('developer_logs', function ($table) {
            $table->id();
            $table->string('level')->default('info');
            $table->text('log')->nullable();
            $table->text('message')->nullable();
            $table->string('exception_class')->nullable();
            $table->text('stack_trace')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('line_number')->nullable();
            $table->string('request_url')->nullable();
            $table->string('request_method')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('queue')->nullable();
            $table->json('context')->nullable();
            $table->json('tags')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    protected function seedTestData()
    {
        DB::table('developer_logs')->insert([
            [
                'id' => 1,
                'level' => 'error',
                'log' => 'Test error message',
                'message' => 'Test error message',
                'exception_class' => 'Exception',
                'file_path' => '/app/test.php',
                'line_number' => 10,
                'status' => 'open',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => 2,
                'level' => 'warning',
                'log' => 'Test warning message',
                'message' => 'Test warning message',
                'status' => 'resolved',
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ],
            [
                'id' => 3,
                'level' => 'info',
                'log' => 'Test info message',
                'message' => 'Test info message',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function createUser($attributes = [])
    {
        return new class($attributes) {
            public $id;
            public $name;
            public $email;

            public function __construct($attributes = [])
            {
                $this->id = $attributes['id'] ?? 1;
                $this->name = $attributes['name'] ?? 'Test User';
                $this->email = $attributes['email'] ?? 'test@example.com';
            }
        };
    }
}