<?php

namespace Onamfc\DevLoggerDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('devlogger-dashboard::dashboard');
    }
}