<?php

namespace Onamfc\DevLoggerDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DevLoggerDashboardMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if current environment is allowed
        $allowedEnvironments = config('devlogger-dashboard.allowed_environments', ['local']);
        if (!app()->environment($allowedEnvironments)) {
            abort(404, 'DevLogger Dashboard is not available in this environment.');
        }

        // Check IP allowlist if configured
        $allowedIps = config('devlogger-dashboard.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            abort(403, 'Access denied from this IP address.');
        }

        // Check authentication if required
        if (config('devlogger-dashboard.require_auth', true)) {
            if (!auth()->check()) {
                return redirect()->guest(route('login'));
            }
        }

        // Check authorization gate if configured
        $gate = config('devlogger-dashboard.authorization_gate');
        if ($gate && Gate::has($gate)) {
            if (!Gate::allows($gate)) {
                abort(403, 'You do not have permission to access the DevLogger Dashboard.');
            }
        }

        return $next($request);
    }
}