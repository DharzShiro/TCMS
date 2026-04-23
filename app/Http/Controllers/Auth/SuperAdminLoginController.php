<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::guard('web')->user()->role !== 'superadmin') {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'You do not have permission to access this area.',
                ]);
            }

            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withInput($request->only('email'))
                     ->withErrors([
                         'email' => 'The provided credentials do not match our records.',
                     ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}