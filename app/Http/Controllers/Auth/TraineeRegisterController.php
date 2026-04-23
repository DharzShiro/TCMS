<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TraineeRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('tenants.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'trainee',
        ]);

        // Notify admin
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title'   => 'New Trainee Registration',
                'message' => "A new trainee '{$request->name}' has registered with email {$request->email}.",
                'link'    => route('admin.trainees.index'),
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect('/trainee/dashboard');
    }
}