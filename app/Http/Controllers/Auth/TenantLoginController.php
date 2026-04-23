<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class TenantLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('tenants.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            ActivityLog::create([
                'tenant_id'   => tenancy()->tenant?->id,
                'tenant_name' => tenancy()->tenant?->name,
                'user_id'     => Auth::user()->id,
                'user_name'   => Auth::user()->name,
                'user_email'  => Auth::user()->email,
                'role'        => Auth::user()->role,
                'action'      => 'login_success',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'success'     => true,
            ]);

            // ✅ Log successful login
            ActivityLogService::log($request, 'login_success', true);

            return match ($user->role) {
                'admin'   => redirect()->intended(route('admin.dashboard')),
                'trainer' => redirect()->intended(route('trainer.dashboard')),
                'trainee' => redirect()->intended(route('trainee.dashboard')),
                default   => redirect()->intended('/'),
            };
        }

        // ✅ Log failed login attempt
        ActivityLogService::log(
            request: $request,
            action: 'login_failed',
            success: false,
            failureReason: 'Invalid credentials',
            userOverride: ['email' => $request->email, 'name' => null, 'role' => null]
        );

        return back()->withInput($request->only('email'))
                     ->withErrors([
                         'email' => 'The provided credentials do not match our records.',
                     ]);
    }

    public function logout(Request $request)
    {
        // ✅ Log logout before clearing session
        ActivityLogService::log($request, 'logout', true);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}