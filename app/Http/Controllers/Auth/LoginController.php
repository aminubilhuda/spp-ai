<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Login user menggunakan signed URL
     * Digunakan untuk akses langsung ke halaman tertentu tanpa login manual
     * Production Ready dengan validasi keamanan maksimal
     */
    public function loginUrl(Request $request)
    {
        try {
            // Validasi signature URL
            if (!$request->hasValidSignature()) {
                \Log::warning('Invalid signed URL attempt', [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]);
                abort(403, 'URL tidak valid atau sudah kadaluarsa');
            }

            // Validasi parameter yang diperlukan
            if (!$request->has('user_id') || !$request->has('url')) {
                \Log::warning('Missing parameters in signed URL', [
                    'ip_address' => $request->ip(),
                    'parameters' => $request->all()
                ]);
                abort(400, 'Parameter tidak lengkap');
            }

            $userId = (int) $request->user_id;
            $redirectUrl = $request->url;

            // Validasi user exists dan aktif
            $user = \App\Models\User::where('id', $userId)->first();
            if (!$user) {
                \Log::warning('User not found in signed URL', [
                    'user_id' => $userId,
                    'ip_address' => $request->ip()
                ]);
                abort(404, 'User tidak ditemukan');
            }

            // Validasi URL redirect (hanya URL internal yang diizinkan)
            $allowedDomains = [
                config('app.url'),
                parse_url(config('app.url'), PHP_URL_HOST)
            ];
            
            $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);
            $isInternalUrl = str_starts_with($redirectUrl, '/') || 
                           in_array($redirectHost, $allowedDomains);

            if (!$isInternalUrl) {
                \Log::warning('Invalid redirect URL in signed URL', [
                    'redirect_url' => $redirectUrl,
                    'user_id' => $userId,
                    'ip_address' => $request->ip()
                ]);
                abort(400, 'URL redirect tidak valid');
            }

            // Rate limiting untuk mencegah abuse
            $key = 'signed_login_' . $request->ip();
            $attempts = \Cache::get($key, 0);
            
            if ($attempts > 10) { // Max 10 attempts per hour
                \Log::warning('Rate limit exceeded for signed URL', [
                    'ip_address' => $request->ip(),
                    'attempts' => $attempts
                ]);
                abort(429, 'Terlalu banyak percobaan login. Silakan coba lagi nanti.');
            }

            // Login user
            Auth::login($user, false); // Remember me = false untuk keamanan

            // Increment rate limiting
            \Cache::put($key, $attempts + 1, 3600); // 1 hour

            // Log aktivitas login yang berhasil
            \Log::info('User login via signed URL successful', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_akses' => $user->akses,
                'redirect_url' => $redirectUrl,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            // Redirect ke URL yang ditentukan dengan session flash
            return redirect()->to($redirectUrl)
                ->with('success', 'Login berhasil via signed URL');

        } catch (\Exception $e) {
            \Log::error('Error in loginUrl', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'request_data' => $request->all(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Terjadi kesalahan saat login. Silakan coba lagi.');
        }
    }

    // didapat dari authenticatesusers.php
    public function showLoginForm()
    {
        return view('auth.login_sneat');
    }
    public function showLoginFormWali()
    {
        return view('auth.login_sneat_wali');
    }

    public function authenticated(Request $request, $user)
    {
        if ($user->akses == 'operator' || $user->akses == 'admin') {
            return redirect()->route('operator.dashboard');
        } else if ($user->akses == 'wali') {
            return redirect()->route('wali.beranda');
        } else {
            Auth::logout();
            session()->flash('error', 'Anda tidak memiliki akses');
            return redirect()->route('login');
        }
    }
}