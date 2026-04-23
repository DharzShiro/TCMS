<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('tenants.admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'in:admin,trainer,trainee'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // ✅ Enforce total user cap (Standard = 5 users total)
        $limits = $request->attributes->get('plan_limits');
        if ($limits && $limits['users'] !== null) {
            // Count all non-superadmin users
            $count = User::whereIn('role', ['admin', 'trainer', 'trainee'])->count();
            if ($count >= $limits['users']) {
                return back()->withInput()
                    ->withErrors(['limit' => "Your plan allows a maximum of {$limits['users']} total users. Please upgrade your plan to add more."]);
            }
        }

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->loadCount(['enrollments' => fn($q) => $q] ?? []);
        return view('tenants.admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('tenants.admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'in:admin,trainer,trainee'],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'min:8', 'confirmed'],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted successfully.');
    }
}