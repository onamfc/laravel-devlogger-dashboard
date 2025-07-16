<?php

namespace Onamfc\DevLoggerDashboard\Tests\Unit\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Onamfc\DevLoggerDashboard\Http\Middleware\DevLoggerDashboardMiddleware;
use Onamfc\DevLoggerDashboard\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DevLoggerDashboardMiddlewareTest extends TestCase
{
    protected DevLoggerDashboardMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new DevLoggerDashboardMiddleware();
    }

    /** @test */
    public function it_allows_access_in_allowed_environment()
    {
        config(['devlogger-dashboard.allowed_environments' => ['testing']]);
        
        $request = Request::create('/devlogger');
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_blocks_access_in_disallowed_environment()
    {
        config(['devlogger-dashboard.allowed_environments' => ['production']]);
        
        $request = Request::create('/devlogger');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('DevLogger Dashboard is not available in this environment.');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /** @test */
    public function it_allows_access_from_allowed_ip()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.allowed_ips' => ['127.0.0.1']
        ]);
        
        $request = Request::create('/devlogger');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_blocks_access_from_disallowed_ip()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.allowed_ips' => ['192.168.1.1']
        ]);
        
        $request = Request::create('/devlogger');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied from this IP address.');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /** @test */
    public function it_redirects_unauthenticated_users_when_auth_required()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.require_auth' => true
        ]);
        
        $request = Request::create('/devlogger');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_allows_authenticated_users_when_auth_required()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.require_auth' => true
        ]);
        
        $user = $this->createUser();
        $this->actingAs($user);
        
        $request = Request::create('/devlogger');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_checks_authorization_gate_when_configured()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.require_auth' => true,
            'devlogger-dashboard.authorization_gate' => 'access-devlogger'
        ]);

        Gate::define('access-devlogger', function ($user) {
            return false;
        });
        
        $user = $this->createUser();
        $this->actingAs($user);
        
        $request = Request::create('/devlogger');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('You do not have permission to access the DevLogger Dashboard.');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /** @test */
    public function it_allows_access_when_gate_passes()
    {
        config([
            'devlogger-dashboard.allowed_environments' => ['testing'],
            'devlogger-dashboard.require_auth' => true,
            'devlogger-dashboard.authorization_gate' => 'access-devlogger'
        ]);

        Gate::define('access-devlogger', function ($user) {
            return true;
        });
        
        $user = $this->createUser();
        $this->actingAs($user);
        
        $request = Request::create('/devlogger');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}