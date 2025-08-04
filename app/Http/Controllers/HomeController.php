<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->user()->akses == 'operator') {
            return redirect()->route('operator.dashboard');
        }
        if (auth()->user()->akses == 'wali') {
            return redirect()->route('wali.beranda');
        }
        return view('home');
    }
}