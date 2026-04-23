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

        if (Auth::guard('tenant')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('tenant')->user();

            ActivityLog::create([
                'tenant_id'   => tenancy()->tenant?->id,
                'tenant_name' => tenancy()->tenant?->name,
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'user_email'  => $user->email,
                'role'        => $user->role,
                'action'      => 'login_success',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'success'     => true,
            ]);

            ActivityLogService::log($request, 'login_success', true);

            return match ($user->role) {
                'admin'   => redirect()->intended(route('admin.dashboard')),
                'trainer' => redirect()->intended(route('trainer.dashboard')),
                'trainee' => redirect()->intended(route('trainee.dashboard')),
                default   => redirect()->intended('/'),
            };
        }

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
        ActivityLogService::log($request, 'logout', true);

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}