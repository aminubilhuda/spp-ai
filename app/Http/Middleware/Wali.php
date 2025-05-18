<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Wali
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is an wali
        if ($request->user()->akses == 'wali') {
           return $next($request);
        }
        
        return redirect()->route('home')->with('error', 'Anda tidak memiliki akses sebagai wali murid.');
    }
}